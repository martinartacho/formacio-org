@extends('campus.shared.layout')

@section('title', __('campus.treasury'))
@section('subtitle', __('campus.treasury_management'))

@section('breadcrumbs')
    <li>
        <div class="flex items-center">
            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
            <a href="{{ route('campus.treasury.teachers.index') }}" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2">
                {{ __('campus.teachers') }} / ({{ $season->name ?? 'Sense temporada' }}) ({{ $teachersWithCourses->count() }} prof.)
            </a>
        </div>
    </li>
@endsection

@section('actions')
    <div class="flex space-x-2">
        @if($teachersWithCourses->count() > 0)
            <a href="{{ route('campus.treasury.teachers.export', 'xlsx') }}?season={{ $selectedSeasonSlug }}"
                class="px-4 py-2 bg-blue-600 text-white rounded">
                ⬇️ Excel
            </a>
            <a href="{{ route('campus.treasury.teachers.export', 'csv') }}?season={{ $selectedSeasonSlug }}"
                class="px-4 py-2 bg-green-600 text-white rounded">
                ⬇️ CSV
            </a>
        @endif
    </div>
@endsection

@section('content')

<!-- Selector de temporada -->
@if($seasons->count() > 0)
<div class="mb-6 p-4 bg-white rounded-lg shadow">
    <form method="GET" action="{{ route('campus.treasury.teachers.index') }}" class="flex items-center space-x-4">
        <label for="season" class="font-medium">Filtrar per temporada:</label>
        <select name="season" id="season" 
                class="border rounded px-3 py-2 w-64"
                onchange="this.form.submit()">
            @foreach($seasons as $s)
                <option value="{{ $s->slug }}" 
                        {{ ($selectedSeasonSlug == $s->slug) ? 'selected' : '' }}>
                    {{ $s->name }} ({{ $s->slug }})
                    @if($s->is_current) - ACTUAL @endif
                </option>
            @endforeach
        </select>
        
        @if($selectedSeasonSlug)
            <a href="{{ route('campus.treasury.teachers.index') }}"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                Netejar filtre
            </a>
        @endif
    </form>
</div>
@endif

@if($teachersWithCourses->count() === 0)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="bi bi-exclamation-triangle text-yellow-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-yellow-700">
                @if(!$season)
                    No hi ha temporada activa actualment. Activa una temporada a la configuració.
                @else
                    No hi ha professors assignats a cursos en la temporada {{ $season->name }}.
                @endif
            </p>
        </div>
    </div>
</div>
@else
<table class="table-auto w-full border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left">Nom</th>
            <th class="p-2 text-left">Email</th>
            <th class="p-2 text-left">Cursos i hores</th>
            <th class="p-2 text-center">RGPD</th>
            <th class="p-2 text-center">Accions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($teachersWithCourses as $teacherData)
            @php
                $user = $teacherData['user'] ?? null;
                $teacherProfile = $teacherData['teacher_profile'] ?? null;
                $courses = $teacherData['courses'] ?? collect();
                $rgpdConsent = $teacherData['rgpd_consent'] ?? null;
            @endphp
            
            @if($user && $teacherProfile)
                <tr class="border-t">
                    <td class="p-2">{{ $user->name }}</td>
                    <td class="p-2">{{ $user->email }}</td>
                    <td class="p-2">
                        @if($courses->count() > 0)
                            <div class="space-y-1">
                                @foreach($courses as $courseData)
                                    <div class="border-l-4 {{ $courseData['has_payment_data'] ? 'border-green-500' : 'border-yellow-500' }} pl-2 py-1 text-sm">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-800">{{ $courseData['course_title'] }}</div>
                                                <div class="text-xs text-gray-600">
                                                    {{ $courseData['course_code'] }} • 
                                                    <span class="font-medium text-blue-600">{{ $courseData['hours_assigned'] }}h</span>/{{ $teacherData['total_hours_assigned'] }}h • 
                                                    <span class="px-1 py-0.5 bg-gray-100 rounded text-xs">{{ __('campus.teacher_role.' . $courseData['role']) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                @if($courseData['has_payment_data'])
                                                    <span class="text-green-600 text-xs">✅</span>
                                                @else
                                                  <!-- Botó per enviar condicions de pagament -->
                                                    @if($teacherData['courses_with_payment'] < $teacherData['total_courses'])
                                                        <form method="POST"
                                                            action="{{ route('campus.treasury.teachers.send-access', [
                                                                    'teacher' => $teacherData['user']->id,
                                                                    'purpose' => 'payments',
                                                                    'courseCode' => $courseData['course_code']
                                                            ]) }}"
                                                            class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                                                💳
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-500 italic text-sm">Sense cursos assignats</span>
                        @endif
                    </td>
                    <td class="p-2 text-center">
                        @if($teacherData['has_rgpd_consent'] && $rgpdConsent)
                            <div class="flex flex-col items-center">
                                <span class="text-green-600 font-medium">✅ Acceptat</span>
                                <span class="text-xs text-gray-500">
                                    {{ $rgpdConsent->accepted_at->format('d/m/Y H:i') }}
                                </span>
                                @if($rgpdConsent->delegated_by_user_id)
                                    <span class="text-xs text-blue-600">(Delegat)</span>
                                @endif
                            </div>
                        @else
                            <span class="text-red-600 font-medium">❌ Pendent</span>
                        @endif
                    </td>
                    <td class="p-2 text-center">
                        <div class="flex flex-col space-y-2">
                            <!-- Botón para ver detalles -->
                            <a href="{{ route('campus.treasury.teachers.show', $user) }}"
                               class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                👁️ Veure
                            </a>
                            
                            <!-- Botón para enviar recordatorio RGPD -->
                            @if(!$teacherData['has_rgpd_consent'])
                                <form method="POST"
                                    action="{{ route('campus.treasury.teachers.send-access', [
                                            'teacher' => $teacherData['user']->id,
                                            'purpose' => 'consent',
                                             'courseCode' => $courseData['course_code']
                                    ]) }}"
                                    class="inline">
                                    @csrf

                                    <button type="submit"
                                            class="px-3 py-1 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                        ⬇️ RGPD
                                    </button>
                                </form>

                                
                            @elseif($rgpdConsent && $rgpdConsent->document_path && $rgpdConsent->document_path !== 'pending')
                                <!-- Descargar consentimiento RGPD -->
                                <a href="{{ route('consents.download', $rgpdConsent) }}"
                                   class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                                    ⬇️ RGPD
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
@endif

@endsection

@push('scripts')
<script>
function sendPaymentReminder(teacherId, seasonSlug) {
    if (confirm('Vols enviar un recordatori de dades de pagament a aquest professor?')) {
        // Implementar lògica per enviar recordatori
        alert('Recordatori enviat al professor ID: ' + teacherId + ' per a la temporada: ' + seasonSlug);
        // Aquí pots fer una crida AJAX si és necessari
    }
}
</script>
@endpush