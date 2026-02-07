@extends('campus.shared.layout')


@section('title', __('campus.treasury'))
@section('subtitle', __('campus.treasury_management')) 

@section('breadcrumbs')
    <li>
        <div class="flex items-center">
            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
            <a href="{{ route('campus.treasury.teachers.index') }}" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2">
               {{ __('campus.teachers')}} /  ({{ $season->name }}) ({{ $teachers->count() }} prof.)
            </a> 
        </div>
    </li>
@endsection


@section('actions')
    <div class="flex space-x-2">
                
        {{-- @if($teachers->count() > 1) --}}
        <a href="{{ route('campus.treasury.teachers.export', 'xlsx') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded">
            ⬇️ Excel
        </a>
        <a href="{{ route('campus.treasury.teachers.export', 'csv') }}"
            class="px-4 py-2 bg-green-600 text-white rounded">
            ⬇️ CSV
        </a>
       {{--  @endif --}}
    </div>
@endsection

@section('content')

{{-- Exemple d'ús a la vista --}}
@if($teachers->count() === 0)
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
                    No hi ha professors assignats a cursos en la temporada actual ({{ $season->name }}).
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
            <th class="p-2 text-left">Temporada</th>
            <th class="p-2 text-center">RGPD</th>
            <th class="p-2 text-center">Accions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($teachers as $teacher)
            <tr class="border-t">
                <td class="p-2">{{ $teacher->name }}</td>
                <td class="p-2">{{ $teacher->email }}</td>
                <td class="p-2">{{ $season->name ?? 'N/A' }}</td>
                <td class="p-2 text-center">
                    @if ($teacher->treasuryData->where('key', 'consent_signed_at')->first())
                        ✅ Acceptat
                    @else
                        ❌ Pendent
                        <form method="POST"
                              action="{{ route('campus.treasury.teachers.send-access', $teacher) }}">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Enviar recordatori
                            </button>
                        </form>
                    @endif
                </td>
                <td class="p-2 text-center">
                    <a href="{{ route('campus.treasury.teachers.show', $teacher) }}"
                       class="text-blue-600 underline">
                        Veure
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@endsection
