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
    public function index()
    {
        $this->authorize('campus.teachers.view');

        $teachers = User::role('teacher')
            ->with('treasuryData')
            ->get();

        return view('treasury.teachers.index', compact('teachers'));
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
            'document_path' => $path,

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
        dd('Pepe en linea 169 de generateConsentPdf');
       
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
                'document_path' => $path,
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
