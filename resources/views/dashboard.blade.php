{{-- resources\views\dashboard.blade.php --}}
@php
    $context = $context ?? session('context');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('site.Dashboard') }} 
        </h2>
    </x-slot>

    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 1. Dashboard Admin --}}
@if(auth()->user()->hasAnyRole(['admin', 'super-admin']))
    <x-dashboard.admin :stats="$stats ?? []" />

{{-- 2. Dashboard Manager --}}
@elseif(auth()->user()->hasAnyRole(['gestor', 'editor', 'manager']))
    <x-dashboard.manager />

{{-- 3. Teacher --}}
@elseif(auth()->user()->hasRole('teacher'))
    <x-dashboard.teacher />

{{-- 4. Student --}}
@elseif(auth()->user()->hasRole('student'))
    <x-dashboard.student />

{{-- 5. Fallback --}}
@else
    <x-dashboard.basic />
@endif

            
            {{-- 3. Dashboard para Profesores --}}
            {{-- @elseif(auth()->user()->hasRole('teacher') || 
                    auth()->user()->canany(['campus.my_courses.manage', 'campus.teacher-students.view']))
                    <x-dashboard.teacher
                        :teacher="$teacher"
                        :season="$season"
                        :seasons="$seasons"
                        :teacher-courses="$teacherCourses"
                        :stats="$stats"
                        :debug="$debug ?? null"
                        :error="$error ?? null"
                    /> --}}
            
        
            
        </div>
    </div>
</x-app-layout>