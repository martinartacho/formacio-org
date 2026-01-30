@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">
    Professorat – Administració Econòmica
</h1>

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
                    <a href="{{ route('treasury.teachers.show', $teacher) }}"
                       class="text-blue-600 underline">
                        Veure
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
