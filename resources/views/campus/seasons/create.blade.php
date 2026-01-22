{{-- resources/views/campus/seasons/create.blade.php --}}
@extends('campus.shared.layout')

@section('title', 'Nova Temporada')
@section('subtitle', 'Crear una nova temporada acadèmica')

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
            <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">Nova</span>
        </div>
    </li>
@endsection

@section('content')
    <form action="{{ route('campus.seasons.store') }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            {{-- Información básica --}}
            <div class="bg-white p-6 rounded-lg border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('site.item') }}Informació bàsica
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nombre --}}
                    <div>
                        <x-input-label for="name" value="Nom de la temporada *" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                     value="{{ old('name') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    
                    {{-- Descripción --}}
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Descripció" />
                        <textarea id="description" name="description" rows="3" 
                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                </div>
            </div>
            
            {{-- Fechas --}}
            <div class="bg-white p-6 rounded-lg border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="bi bi-calendar-range me-2"></i>
                    {{ __('site.item') }}Dates de la temporada
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Fecha inicio --}}
                    <div>
                        <x-input-label for="season_start" value="Data d'inici *" />
                        <x-text-input id="season_start" name="season_start" type="date" 
                                    class="mt-1 block w-full" value="{{ old('season_start') }}" required />                  
                    </div>
                    
                    {{-- Fecha fin --}}
                    <div>
                       <x-input-label for="season_end" value="Data de finalització *" />
                        <x-text-input id="season_end" name="season_end" type="date" 
                                    class="mt-1 block w-full" value="{{ old('season_end') }}" required />  
                    </div>
                </div>
            </div>
            
            {{-- Estado --}}
            <div class="bg-white p-6 rounded-lg border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="bi bi-toggle-on me-2"></i>
                    {{ __('site.item') }}Estat de la temporada
                </h3>
                
                <div class="space-y-4">
                    {{-- Activa --}}
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="is_active" name="is_active" type="checkbox" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-gray-700">{{ __('site.item') }}Temporada activa</label>
                            <p class="text-gray-500">{{ __('site.item') }}La temporada estarà disponible per a nous cursos i matriculacions.</p>
                        </div>
                    </div>
                    
                    {{-- Actual --}}
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="is_current" name="is_current" type="checkbox" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                   value="1" {{ old('is_current') ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_current" class="font-medium text-gray-700">{{ __('site.item') }}Temporada actual</label>
                            <p class="text-gray-500">{{ __('site.item') }}Aquesta serà la temporada que es mostrarà per defecte en el sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Botones --}}
            <div class="flex justify-end space-x-4">
                <a href="{{ route('campus.seasons.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-x-lg me-2"></i>
                    {{ __('site.item') }}Cancel·lar
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-check-lg me-2"></i>
                    {{ __('site.item') }}Crear Temporada
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación de fechas
        const startDate = document.getElementById('season_start');
        const endDate = document.getElementById('season_end');
        
        function validateDates() {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                
                if (end < start) {
                    endDate.setCustomValidity('La data de finalització ha de ser posterior a la data d\'inici.');
                } else {
                    endDate.setCustomValidity('');
                }
            }
        }
        
        startDate.addEventListener('change', validateDates);
        endDate.addEventListener('change', validateDates);
        
        // Marcar como actual también marca como activa
        const isCurrent = document.getElementById('is_current');
        const isActive = document.getElementById('is_active');
        
        if (isCurrent && isActive) {
            isCurrent.addEventListener('change', function() {
                if (this.checked) {
                    isActive.checked = true;
                }
            });
        }
    });
</script>
@endpush