<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TreasuryData;
use App\Models\ConsentHistory;
use App\Models\CampusSeason;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeachersRgpdExport;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherTreasuryController extends Controller
{
/*     public function index()
    {
        $this->authorize('campus.teachers.view');

        $teachers = User::role('teacher')
            ->with('treasuryData')
            ->get();
           
        return view('treasury.teachers.index', compact('teachers'));
    } */

/*     public function index()
    {
        $this->authorize('campus.teachers.view');

        // Obtenir la temporada actual
        $season = CampusSeason::where('is_current', true)->first();

        if (!$season) {
            $teachers = collect();
        } else {
            $teachers = User::role('teacher')
                ->join('campus_teachers', 'users.id', '=', 'campus_teachers.user_id')
                ->join('campus_course_teacher', 'campus_teachers.id', '=', 'campus_course_teacher.teacher_id')
                ->join('campus_courses', 'campus_course_teacher.course_id', '=', 'campus_courses.id')
                ->where('campus_courses.season_id', $season->id)
                ->select('users.*')
                ->distinct()
                ->with('treasuryData')
                ->get();
        }
        return view('treasury.teachers.index', compact('teachers', 'season'));
        // return view('treasury.teachers.index', compact('teachers'));
    } */
public function index(Request $request)
{
    $this->authorize('campus.teachers.view');

    // Obtenir totes les temporades per al selector
    $seasons = CampusSeason::orderBy('season_start', 'desc')->get();
    
    // Obtenir la temporada seleccionada o l'actual
    $selectedSeasonSlug = $request->input('season');
    
    if ($selectedSeasonSlug) {
        $selectedSeason = CampusSeason::where('slug', $selectedSeasonSlug)->first();
    } else {
        $selectedSeason = CampusSeason::where('is_current', true)->first();
        $selectedSeasonSlug = $selectedSeason->slug ?? null;
    }

    // Inicialitzar variables
    $teachersWithCourses = collect();
    $courseAssignments = collect();

    if ($selectedSeason) {
        // Obtenir assignacions de cursos per a la temporada seleccionada
        $courseAssignments = \App\Models\CampusCourseTeacher::whereHas('course', function($query) use ($selectedSeason) {
            $query->where('season_id', $selectedSeason->id);
        })
        ->with(['teacher.user', 'course', 'teacher.consents' => function($query) use ($selectedSeason) {
            $query->where('season', $selectedSeason->slug);
        }])
        ->get();

        // Agrupar per professor
        $groupedAssignments = $courseAssignments->groupBy('teacher_id');

        // Processar les dades per a cada professor
        $teachersWithCourses = $groupedAssignments->map(function($assignments, $teacherId) use ($selectedSeason) {
            $teacher = $assignments->first()->teacher;
            $user = $teacher->user;
            
            // Obtenir consentiment RGPD per a aquesta temporada
            $rgpdConsent = $teacher->consents->first();
            
            // Obtenir dades de pagament per a cada curs
            $coursesWithPayment = $assignments->map(function($assignment) use ($selectedSeason) {
                $payment = \App\Models\CampusTeacherPayment::where('teacher_id', $assignment->teacher_id)
                    ->where('course_id', $assignment->course_id)
                    ->where('season_id', $selectedSeason->id)
                    ->first();
                
                return [
                    'assignment' => $assignment,
                    'course' => $assignment->course,
                    'payment' => $payment,
                    'has_payment_data' => $payment !== null,
                ];
            });

            // Calcular estats globals
            $hasRgpdConsent = $rgpdConsent !== null;
            $allCoursesHavePayment = $coursesWithPayment->every(fn($item) => $item['has_payment_data']);
            $someCoursesHavePayment = $coursesWithPayment->contains(fn($item) => $item['has_payment_data']);

            return [
                'teacher' => $teacher,
                'user' => $user,
                'courses' => $coursesWithPayment,
                'rgpd_consent' => $rgpdConsent,
                'has_rgpd_consent' => $hasRgpdConsent,
                'all_courses_have_payment' => $allCoursesHavePayment,
                'some_courses_have_payment' => $someCoursesHavePayment,
                'total_courses' => $coursesWithPayment->count(),
                'courses_with_payment' => $coursesWithPayment->filter(fn($item) => $item['has_payment_data'])->count(),
            ];
        });
    }

    return view('treasury.teachers.index', compact(
        'teachersWithCourses', 
        'seasons', 
        'selectedSeason', 
        'selectedSeasonSlug',
        'courseAssignments'
    ));
}
    
    public function show(User $teacher)
    {
        $this->authorize('campus.teachers.financial_data.view');
        
        /*
        $season = CampusSeason::where('is_current', true)->first();
        $currentSeason = CampusSeason::current()->first();
        */
        
        $teacher->load('treasuryData');

        return view('treasury.teachers.show', compact('teacher'));
    }

    public function storeConsent(Request $request, User $teacher)
    {
        if (
            auth()->id() !== $teacher->id &&
            ! auth()->user()->can('campus.consents.request')
        ) {
            abort(403);
        }

        $season = CampusSeason::where('is_current', true)->first();
        $currentSeason = CampusSeason::current()->first();

        $seasonCode = $season->slug;
        $acceptedAt = now();

        $checksum = hash('sha256', implode('|', [
            $teacher->id,
            $seasonCode,
            $acceptedAt->timestamp,
            auth()->id(),
        ]));

        $pdf = Pdf::loadView('pdfs.teacher-consent', [
            'teacher' => $teacher,
            'season' => $season,
            'acceptedAt' => $acceptedAt,
            'delegatedBy' => auth()->id() !== $teacher->id ? auth()->user() : null,
            'delegatedReason' => auth()->id() !== $teacher->id
                 ? $request->input('delegated_reason') // CORREGIDO
                : null,
            'checksum' => $checksum,
        ]);
        
        $path = "consents/teachers/{$teacher->id}/{$seasonCode}.pdf";  // ubicacio de la plantilla
        
        Storage::disk('local')->put(  // guarda el pdf a la ubicacio de $path
            $path,
            $pdf->output()
        );
        // guarda a la tabla
        ConsentHistory::updateOrCreate([
            'teacher_id' => $teacher->id,
            'season' => $seasonCode,
            'accepted_at' => $acceptedAt,
            'checksum' => $checksum,
            'document_path' => $path ?? null, 

            // Delegació
            'delegated_by_user_id' => auth()->id() !== $teacher->id
                ? auth()->id()
                : null,

            'delegated_reason' => auth()->id() !== $teacher->id
                ? $request->input('delegated_reason') // CORREGIT
                : null,
        ]);
        // y cudar a la taula
        TreasuryData::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'key' => 'consent_signed_at',
            ],
            [
                'value' => $acceptedAt->toDateTimeString(),
            ]
        );
        
        return redirect()
            ->route('campus.treasury.teachers.show', $teacher)
            ->with('success', 'Consentiment RGPD registrat correctament.');
    }

    public function export(string $format)
    {
        $this->authorize('campus.teachers.financial_data.view');

        $season = CampusSeason::where('is_current', true)->firstOrFail();
        $seasonCode = $season->slug;

        if ($format === 'csv') {
            return $this->exportCsv();
        }

        return Excel::download(
            new TeachersRgpdExport($seasonCode),
            "teachers_rgpd_{$seasonCode}.xlsx"
        );
    }



    public function exportCsv(): StreamedResponse
    {
        $this->authorize('campus.teachers.financial_data.view');

        $season = CampusSeason::where('is_current', true)->firstOrFail();
        $seasonCode = $season->slug;

        $filename = "teachers_rgpd_{$seasonCode}.csv";

        return response()->streamDownload(function () use ($seasonCode) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'teacher_id',
                'name',
                'email',
                'rgpd_status',
                'rgpd_accepted_at',
                'delegated',
                'delegated_by',
            ]);

            User::role('teacher')
                ->with(['consents' => function ($q) use ($seasonCode) {
                    $q->where('season', $seasonCode);
                }])
                ->orderBy('name')
                ->chunk(100, function ($teachers) use ($handle) {

                    foreach ($teachers as $teacher) {

                        $consent = $teacher->consents->first();

                        fputcsv($handle, [
                            $teacher->id,
                            $teacher->name,
                            $teacher->email,
                            $consent ? 'ACCEPTED' : 'PENDING',
                            $consent?->accepted_at?->format('Y-m-d H:i'),
                            $consent && $consent->delegated_by_user_id ? 'YES' : 'NO',
                            $consent?->delegated_by_user_id,
                        ]);
                    }
                });

            fclose($handle);

        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function generateConsentPdf(User $teacher)
    {
        $this->authorize('consents.request');
       //  dd('nota viernes Pepe en linea 169 de generateConsentPdf');
       
        $season = CampusSeason::where('is_current', true)->first();
        $currentSeason = CampusSeason::current()->first();


        $existing = ConsentHistory::where('teacher_id', $teacher->id)
            ->where('season', $season)
            ->first();

        if ($existing) {
            return redirect()
                ->route('treasury.teachers.show', $teacher)
                ->with('success', 'El consentiment d’aquesta temporada ja existeix.');
        }


        $data = $teacher->treasuryData
            ->pluck('value', 'key')
            ->toArray();

        $acceptedAt = now();

        $pdf = Pdf::loadView('treasury.consents.pdf', [
            'teacher' => $teacher,
            'season' => $season,
            'data' => $data,
            'acceptedAt' => $acceptedAt,
        ]);

        $path = "consents/{$season}/teacher_{$teacher->id}.pdf";

        Storage::disk('private')->put($path, $pdf->output());

        $checksum = hash('sha256', Storage::disk('private')->get($path));

        ConsentHistory::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'season' => $season,
            ],
            [
                'document_path' => $path ?? null,
                'accepted_at' => $acceptedAt,
                'checksum' => $checksum,
            ]
        );

        return redirect()
            ->route('treasury.teachers.show', $teacher)
            ->with('success', 'Consentiment PDF generat i registrat.');
    }


    public function consentHistory(User $teacher)
    {
        $this->authorize('consents.view');
        
        dd('Pepe en 152 de consentHistory');

        $consents = ConsentHistory::where('teacher_id', $teacher->id)
            ->orderByDesc('season')
            ->get();


        return view('treasury.teachers.consents', compact('teacher', 'consents'));
    }



    public function downloadConsent(ConsentHistory $consent)
    {
       
       
        if (
            auth()->id() !== $consent->teacher_id &&
            ! auth()->user()->can('campus.consents.view')
        ) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($consent->document_path)) {
            abort(404, 'Document no trobat');
        }
     

        return Storage::disk('local')->download(
            $consent->document_path,
            basename($consent->document_path)
        );
    }



}
