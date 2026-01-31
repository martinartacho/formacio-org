@extends('campus.shared.layout')


@section('title', __('campus.treasury'))
@section('subtitle', __('campus.Gestió professors (consentiments RGPD)'))

@section('breadcrumbs')
    <li>
        <div class="flex items-center">
            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
            <a href="{{ route('campus.treasury.teachers.index') }}" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2">
               {{ __('campus.teachers')}}
            </a>
        </div>
    </li>
@endsection


@section('actions')
    <div class="flex space-x-2">
                
        @if($teachers->count() > 5)
        <a href="{{ route('campus.treasury.teachers.export', 'xlsx') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded">
            ⬇️ Excel
        </a>
        <a href="{{ route('campus.treasury.teachers.export', 'csv') }}"
            class="px-4 py-2 bg-green-600 text-white rounded">
            ⬇️ CSV
        </a>
        @endif
    </div>
@endsection

@section('content')


<table class="table-auto w-full border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left">Nom</th>
            <th class="p-2 text-left">Email</th>
            <th class="p-2 text-center">RGPD</th>
            <th class="p-2 text-center">Accions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($teachers as $teacher)
            <tr class="border-t">
                <td class="p-2">{{ $teacher->name }}</td>
                <td class="p-2">{{ $teacher->email }}</td>
                <td class="p-2 text-center">
                    @if ($teacher->treasuryData->where('key', 'consent_signed_at')->first())
                        ✅ Acceptat
                    @else
                        ❌ Pendent
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

@endsection
