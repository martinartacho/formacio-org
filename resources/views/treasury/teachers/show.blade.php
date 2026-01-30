@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">
    Professor – Dades Econòmiques
</h1>

@if (session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
@endif

<div class="mb-6">
    <h2 class="font-medium text-lg mb-2">Dades bàsiques</h2>

    <ul class="text-sm text-gray-700">
        <li><strong>Nom:</strong> {{ $teacher->name }}</li>
        <li><strong>Email:</strong> {{ $teacher->email }}</li>
    </ul>
</div>

<div class="mb-6">
    <h2 class="font-medium text-lg mb-2">Estat RGPD</h2>

    @php
        $consent = $teacher->treasuryData
            ->where('key', 'consent_signed_at')
            ->first();
    @endphp

    @if ($consent)
        <p class="text-green-700">
            ✅ Consentiment acceptat el {{ \Carbon\Carbon::parse($consent->value)->format('d/m/Y H:i') }}
        </p>
    @else
        <p class="text-red-700 mb-4">
            ❌ Consentiment pendent
        </p>

        <form method="POST" action="{{ route('treasury.teachers.consent.store', $teacher) }}">
            @csrf
            <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
                Registrar consentiment RGPD
            </button>
        </form>
    @endif
</div>

<a href="{{ route('treasury.teachers.index') }}"
   class="text-sm text-gray-600 underline">
    ← Tornar al llistat
</a>
@endsection
