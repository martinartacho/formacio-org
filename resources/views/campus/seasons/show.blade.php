{{-- resources/views/campus/seasons/show.blade.php --}}
@extends('campus.shared.layout')

@section('title', $season->name)
@section('subtitle', 'Detalls de la temporada acadèmica')

@section('breadcrumbs')
    <li>
        <div class="flex items-center">
            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
            <a href="{{ route('campus.seasons.index') }}" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2">
                {{ __('site.item') }}Temporades
            </a>
        </div>
    </li>
    <li aria-current="page">
        <div class="flex items-center">
            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
            <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">{{ __('site.item') }}Detalls</span>
        </div>
    </li>
@endsection

@section('actions')
    <div class="flex space-x-2">
        <a href="{{ route('campus.seasons.edit', $season) }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <i class="bi bi-pencil me-2"></i>
            {{ __('site.item') }}Editar
        </a>
        <form action="{{ route('campus.seasons.destroy', $season) }}" method="POST" 
              onsubmit="return confirm('Estàs segur que vols eliminar aquesta temporada?')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="bi bi-trash me-2"></i>
                {{ __('site.item') }}Eliminar
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Información general --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Datos principales --}}
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $season->name }}</h3>
                            @if($season->description)
                                <p class="text-gray-600 mb-4">{{ $season->description }}</p>
                            @endif
                            
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm {{ $season->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="bi bi-{{ $season->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                                    {{ $season->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                                
                                @if($season->is_current)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                        <i class="bi bi-star-fill me-1"></i>
                                        {{ __('site.item') }}Temporada actual
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ __('site.item') }}ID</div>
                            <div class="text-lg font-semibold text-gray-900">#{{ $season->id }}</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg me-3">
                                    <i class="bi bi-calendar-date text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-blue-800">{{ __('site.item') }}Data d'inici</div>
                                    <div class="font-semibold text-gray-900">{{ $season->season_start->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg me-3">
                                    <i class="bi bi-calendar-date text-green-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-green-800">{{ __('site.item') }}Data de finalització</div>
                                    <div class="font-semibold text-gray-900">{{ $season->season_end->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Estadísticas --}}
            <div>
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="bi bi-graph-up me-2"></i>
                        {{ __('site.item') }}Estadístiques
                    </h4>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('site.item') }}Cursos totals</span>
                            <span class="font-semibold">{{ $stats['total_courses'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('site.item') }}Cursos actius</span>
                            <span class="font-semibold">{{ $stats['active_courses'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('site.item') }}Matriculacions</span>
                            <span class="font-semibold">{{ $stats['total_registrations'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('site.item') }}Estudiants únics</span>
                            <span class="font-semibold">{{ $stats['unique_students'] }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">Creat el</div>
                        <div class="font-medium">{{ $season->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Cursos de esta temporada --}}
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="bi bi-book me-2"></i>
                    {{ __('site.item') }}Cursos d'aquesta temporada
                </h3>
                
                <a href="{{ route('campus.courses.create') }}?season={{ $season->id }}" 
                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('site.item') }}Nou Curs
                </a>
            </div>
            
            @if($season->courses->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <i class="bi bi-book text-3xl mb-3"></i>
                    <p>{{ __('site.item') }}No hi ha cursos en aquesta temporada.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('site.item') }}Curs
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('site.item') }}Categoria
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('site.item') }}Professors
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('site.item') }}Matriculacions
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('site.item') }}Estat
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($season->courses as $course)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4">
                                    <a href="{{ route('campus.courses.show', $course) }}" 
                                       class="font-medium text-blue-600 hover:text-blue-800">
                                        {{ $course->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-4">
                                    @if($course->category)
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100">
                                            {{ $course->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Sense categoria</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($course->teachers->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($course->teachers as $teacher)
                                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                    {{ $teacher->user->name ?? $teacher->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">{{ __('site.item') }}Sense professors</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-medium">{{ $course->registrations_count }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    @if($course->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('site.item') }}Actiu
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('site.item') }}Inactiu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        {{-- Acciones adicionales --}}
        <div class="flex justify-end space-x-4">
            @if(!$season->is_current)
                <form action="{{ route('campus.seasons.setAsCurrent', $season) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="bi bi-star me-2"></i>
                        {{ __('site.item') }}Marcar com a actual
                    </button>
                </form>
            @endif
            
            <form action="{{ route('campus.seasons.toggleActive', $season) }}" method="POST">
                @csrf

                <a href="{{ route('campus.seasons.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-x-lg me-2"></i>
                     {{ __('Cancel') }}
                </a>

                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 {{ $season->is_active ? 'bg-red-600' : 'bg-green-600' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:{{ $season->is_active ? 'bg-red-700' : 'bg-green-700' }} focus:{{ $season->is_active ? 'bg-red-700' : 'bg-green-700' }} active:{{ $season->is_active ? 'bg-red-800' : 'bg-green-800' }} focus:outline-none focus:ring-2 focus:ring-{{ $season->is_active ? 'red' : 'green' }}-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-{{ $season->is_active ? 'x-circle' : 'check-circle' }} me-2"></i>
                    {{ $season->is_active ? 'Desactivar' : 'Activar' }}
                </button>
            </form>
        </div>
    </div>
@endsection