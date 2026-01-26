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
            @lang('campus.teacher') @lang('campus.no_records')
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
            <div class="text-xs text-gray-500">@lang('campus.total_courses_teacher')</div>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">{{ $stats['total_students'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">@lang('campus.total_students_teacher')</div>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">{{ $stats['today_classes'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">@lang('campus.today_classes_teacher')</div>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">{{ $stats['upcoming_registrations'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">@lang('campus.pending_registrations_teacher')</div>
        </div>
    </div>

    {{-- CURSOS --}}
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-semibold mb-4">@lang('campus.my_courses')</h2>

        @if($teacherCourses->isEmpty())
            <p class="text-gray-500">
                @lang('campus.no_courses')
            </p>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($teacherCourses as $course)
                    @php
                        // Necesito información adicional para completar esto
                        // Por ahora muestro lo básico
                        $studentCount = $course->confirmed_students_count ?? 0;
                        $courseStatus = $course->isCurrentlyActive() ? 'active' : 
                                       ($course->start_date > now() ? 'upcoming' : 'completed');
                    @endphp
                    
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        {{-- Header del curso --}}
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="font-semibold text-lg text-gray-800">
                                    {{ $course->title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    @lang('campus.course_code'): {{ $course->code }}
                                </div>
                            </div>
                            
                            {{-- Badge de status --}}
                            @if($courseStatus === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    @lang('campus.course_status_active')
                                </span>
                            @elseif($courseStatus === 'upcoming')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    @lang('campus.course_status_upcoming')
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    @lang('campus.course_status_completed')
                                </span>
                            @endif
                        </div>

                        {{-- Información básica --}}
                        <div class="space-y-2 mb-4">
                            {{-- Rol y horas asignadas --}}
                            <div class="flex items-center text-sm text-gray-600">
                                <span class="font-medium mr-2">@lang('campus.role'):</span>
                                <span class="mr-4">
                                    {{ trans('campus.teacher_role.' . ($course->pivot->role ?? 'assistant')) }}
                                </span>
                                <span class="font-medium mr-2">@lang('campus.course_hours_assigned'):</span>
                                <span>{{ $course->pivot->hours_assigned ?? 0 }}</span>
                            </div>

                            {{-- Fechas --}}
                            <div class="text-sm text-gray-600">
                                <span class="font-medium mr-2">@lang('campus.dates'):</span>
                                {{ $course->start_date?->format('d/m/Y') }} - {{ $course->end_date?->format('d/m/Y') }}
                            </div>

                            {{-- Estudiantes --}}
                            <div class="text-sm text-gray-600">
                                <span class="font-medium mr-2">@lang('campus.students'):</span>
                                <span>{{ $studentCount }}</span>
                                @if($course->max_students)
                                    <span class="text-gray-500 ml-2">
                                        (@lang('campus.available_spots', [
                                            'available' => $course->available_spots ?? $course->max_students - $studentCount,
                                            'total' => $course->max_students
                                        ]))
                                    </span>
                                @endif
                            </div>

                            {{-- TODO: Otros profesores --}}
                            {{-- <div class="text-sm text-gray-600">
                                <span class="font-medium mr-2">@lang('campus.other_teachers'):</span>
                                @if($course->teachers->count() > 1)
                                    {{ $course->teachers->where('id', '!=', $teacher->id)->count() }} @lang('campus.teachers')
                                @else
                                    <span class="text-gray-500">@lang('campus.no_other_teachers')</span>
                                @endif
                            </div> --}}
                        </div>

                        {{-- Acciones --}}
                        <div class="flex flex-wrap gap-2 pt-3 border-t">
                            {{-- Enlace a estudiantes --}}
                            <a href="{{-- route('teacher.course.students', $course->id) --}}" 
                               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100">
                                @lang('campus.view_students')
                            </a>

                            {{-- Enlace a materiales --}}
                            <button type="button" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100"
                                    disabled>
                                @lang('campus.materials_pending')
                            </button>

                            {{-- Más información del curso --}}
                            <a href="{{-- route('courses.show', $course->id) --}}" 
                               class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100">
                                @lang('campus.view')
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>