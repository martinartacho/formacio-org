{{-- Gestió de events --}}
@can('events.view')
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-bold">{{ __('Gestió de eventss') }} </h2>
    {{-- Contenido específico para gestores --}}
</div>
@endcan

{{-- Gestión del campus --}}
@can('campus.courses.view')
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-bold">{{ __('Gestión del Campus') }}</h2>
    {{-- Contenido específico --}}
</div>
@endcan