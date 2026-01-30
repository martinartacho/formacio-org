<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TreasuryData;
use App\Models\ConsentHistory;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherTreasuryController extends Controller
{
    public function index()
    {
        $this->authorize('teachers.view');

        $teachers = User::role('teacher')
            ->with('treasuryData')
            ->get();

        return view('treasury.teachers.index', compact('teachers'));
    }

    public function show(User $teacher)
    {
        $this->authorize('teachers.financial_data.view');

        $teacher->load('treasuryData');

        return view('treasury.teachers.show', compact('teacher'));
    }

    public function storeConsent(Request $request, User $teacher)
    {
        $this->authorize('consents.request');

        TreasuryData::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'key' => 'consent_signed_at',
            ],
            [
                'value' => now()->toDateTimeString(),
            ]
        );

        return redirect()
            ->route('treasury.teachers.show', $teacher)
            ->with('success', 'Consentiment RGPD registrat correctament.');
    }


    public function exportCsv(): Response
    {
        $season = current_season(); // helper existent

        $teachers = User::role('teacher')
            ->with(['consents' => function ($q) use ($season) {
                $q->where('season', $season);
            }])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="teachers_rgpd_'.$season.'.csv"',
        ];

        $callback = function () use ($teachers, $season) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'teacher_id',
                'name',
                'email',
                'season',
                'rgpd_accepted',
                'accepted_at',
            ]);

            foreach ($teachers as $t) {
                $consent = $t->consents->first();

                fputcsv($out, [
                    $t->id,
                    $t->name,
                    $t->email,
                    $season,
                    $consent ? 'yes' : 'no',
                    $consent?->accepted_at,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generateConsentPdf(User $teacher)
    {
        $this->authorize('consents.request');

        $season = config('campus.current_season', '2025-2026');

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

        $consents = ConsentHistory::where('teacher_id', $teacher->id)
            ->orderByDesc('season')
            ->get();

        return view('treasury.teachers.consents', compact('teacher', 'consents'));
    }

    public function downloadConsent(ConsentHistory $consent)
    {
        $this->authorize('consents.view');

        return Storage::disk('private')->download($consent->document_path);
    }



}
