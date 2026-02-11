{{-- resources/views/components/dashboard/teacher.blade.php --}}

@props([
    'teacher' => null,
    'season' => null,
    'seasons' => collect(),
    'teacherCourses' => collect(),
    'stats' => [],
    'debug' => null,
    'error' => null,
])

<div class="space-y-6">

    {{-- CARDS SUPERIORES --}}
    @include('components.dashboard-teacher-cards')

    {{-- DEBUG --}}
    @if(config('app.debug'))
        <pre class="bg-gray-100 p-3 text-xs rounded border">
        {{ var_export([
            'teacher' => optional($teacher)->teacher_code,
            'courses' => $teacherCourses->count(),
            'stats' => $stats,
        ], true) }}
        </pre>
    @endif

    {{-- ERROR GLOBAL --}}
    @if($error)
        <div class="bg-red-100 text-red-800 p-4 rounded">
            {{ $error }}
        </div>
        @return
    @endif

    {{-- PERFIL NO ENCONTRADO --}}
    @if(!$teacher)
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
            Perfil de profesor no encontrado
        </div>
        @return
    @endif

    {{-- HEADER --}}
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold">
            {{ auth()->user()->name }} — {{ $teacher->teacher_code }}
        </h1>

        <p class="text-sm text-gray-500">
            {{ $season?->name }}
        </p>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">{{ $stats['total_courses'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">Cursos</div>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">{{ $stats['total_students'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">Estudiantes</div>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">{{ $stats['today_classes'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">Clases hoy</div>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">{{ $stats['upcoming_registrations'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">Matrículas pendientes</div>
        </div>
    </div>

    {{-- CURSOS --}}
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-semibold mb-4">Mis cursos</h2>

        @if($teacherCourses->isEmpty())
            <p class="text-gray-500">
                No tienes cursos asignados.
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($teacherCourses as $course)
                    <div class="border rounded p-4">
                        <div class="font-semibold">
                            {{ $course->title }}
                        </div>

                        <div class="text-sm text-gray-600">
                            Rol: {{ $course->pivot->role }}
                        </div>

                        <div class="text-sm text-gray-600">
                            Horas asignadas: {{ $course->pivot->hours_assigned }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
