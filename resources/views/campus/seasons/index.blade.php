{{-- resources/views/campus/seasons/index.blade.php --}}
@extends('campus.shared.layout')

@section('title', 'Temporades')
@section('subtitle', 'Gestió de temporades acadèmiques')

@section('breadcrumbs')
    <li aria-current="page">
        <div class="flex items-center">
            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
            <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">{{ __('site.item') }}Temporades</span>
        </div>
    </li>
@endsection

@section('actions')
    <a href="{{ route('campus.seasons.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
        <i class="bi bi-plus-lg me-2"></i>
        Nova Temporada
    </a>
@endsection

@section('content')
    <div class="overflow-x-auto">
        @if($seasons->isEmpty())
            <div class="text-center py-12">
                <i class="bi bi-calendar-x text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('site.item') }}No hi ha temporades</h3>
                <p class="text-gray-600 mb-6">{{ __('site.item') }}Crea la primera temporada acadèmica per començar.</p>
                <a href="{{ route('campus.seasons.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="bi bi-plus-lg me-2"></i>
                    {{ __('site.item') }}Crear Temporada
                </a>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('site.item') }}Nom
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('site.item') }}Dates
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('site.item') }}Estat
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('site.item') }}Cursos
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('site.item') }}Accions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($seasons as $season)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-calendar-range text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $season->name }}
                                        @if($season->is_current)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="bi bi-star-fill me-1"></i> {{ __('site.item') }}Actual
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">ID: {{ $season->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <div class="flex items-center">
                                    <i class="bi bi-calendar-date me-2 text-gray-400"></i>
                                    {{ $season->season_start->format('d/m/Y') }}
                                </div>
                                <div class="flex items-center mt-1">
                                    <i class="bi bi-calendar-date me-2 text-gray-400"></i>
                                    {{ $season->season_end->format('d/m/Y') }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($season->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('site.item') }}Activa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="bi bi-x-circle me-1"></i> {{ __('site.item') }}Inactiva
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                <i class="bi bi-book me-1"></i>
                                {{ $season->courses_count ?? 0 }} {{ __('site.item') }}cursos
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('campus.seasons.edit', $season) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('campus.seasons.show', $season) }}" 
                                   class="text-green-600 hover:text-green-900"
                                   title="Veure">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('campus.seasons.destroy', $season) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Estàs segur que vols eliminar aquesta temporada?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{-- Paginación --}}
            <div class="mt-6">
                {{ $seasons->links() }}
            </div>
        @endif
    </div>
@endsection
