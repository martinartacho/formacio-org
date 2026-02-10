@extends('campus.shared.layout')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="p-4 bg-white shadow rounded">
        <div class="text-gray-500 text-sm">Professors totals</div>
        <div class="text-2xl font-bold">{{ $data['teachers_total'] }}</div>
    </div>

    <div class="p-4 bg-red-50 shadow rounded">
        <div class="text-gray-500 text-sm">RGPD pendent (temporada actual)</div>
        <div class="text-2xl font-bold text-red-600">
            {{ $data['teachers_pending_rgpd'] }}
        </div>
    </div>

    <div class="p-4 bg-green-50 shadow rounded">
        <div class="text-gray-500 text-sm">RGPD acceptat</div>
        <div class="text-2xl font-bold text-green-700">
            {{ $data['teachers_with_rgpd'] }}
        </div>
    </div>

</div>

<div class="mt-8 bg-white shadow rounded p-4">
    <h2 class="text-lg font-semibold mb-4">Últims consentiments RGPD</h2>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th>Professor</th>
                <th>Temporada</th>
                <th>Acceptat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['last_consents'] as $consent)
                <tr class="border-b">
                    <td>{{ $consent->teacher->name }}</td>
                    <td>{{ $consent->season }}</td>
                    <td>{{ $consent->accepted_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        <a href="{{ route('campus.treasury.teachers.index') }}"
           class="text-blue-600 hover:underline">
            → Gestió professors (Tresoreria)
        </a>
    </div>
</div>

@endsection
