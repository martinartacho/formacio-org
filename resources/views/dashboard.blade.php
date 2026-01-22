{{-- resources\views\dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('site.Dashboard') }} 
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. Dashboard para Administradores/Superusuarios --}}
            @if(auth()->user()->hasAnyRole(['admin', 'super-admin']) || 
                auth()->user()->canany(['users.view', 'roles.index', 'permissions.index', 'settings.edit']))
                <x-dashboard.admin :stats="$stats ?? []" />
            
            {{-- 2. Dashboard para Gestores/Editores --}}
            @elseif(auth()->user()->hasAnyRole(['gestor', 'editor', 'manager']) ||
                    auth()->user()->canany(['events.view', 'campus.categories.view', 'campus.courses.view']))
                <x-dashboard.manager />
            
            {{-- 3. Dashboard para Profesores --}}
            @elseif(auth()->user()->hasRole('teacher') || 
                    auth()->user()->canany(['campus.my_courses.manage', 'campus.teacher-students.view']))
                <x-dashboard.teacher />
            
            {{-- 4. Dashboard para Estudiantes --}}
            @elseif(auth()->user()->hasRole('student') || 
                    auth()->user()->can('campus.my_courses.view'))
                <x-dashboard.student />
            
            {{-- 5. Dashboard básico para usuarios genéricos --}}
            @else
                <x-dashboard.basic />
            @endif
            
        </div>
    </div>
</x-app-layout>