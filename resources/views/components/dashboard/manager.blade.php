@props([
    'stats' => [],
    'debug' => null,
    'error' => null,
])

<div class="bg-white p-6 rounded shadow">
    <h3 class="text-lg font-semibold mb-4">
        Gestió del Campus (resources\views\components\dashboard\manager.blade.php)
    </h3>

    <ul class="list-disc ml-6 text-sm text-gray-700">
        @isset($stats['courses'])
            <li>Cursos: {{ $stats['courses'] }}</li>
        @endisset

        @isset($stats['teachers'])
            <li>Professors: {{ $stats['teachers'] }}</li>
        @endisset

        @isset($stats['students'])
            <li>Alumnes: {{ $stats['students'] }}</li>
        @endisset

        @isset($stats['registrations'])
            <li>Inscripcions: {{ $stats['registrations'] }}</li>
        @endisset
    </ul>
</div>
