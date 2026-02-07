<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <title>Consentiment RGPD {{ $season }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 20px; }
        p { margin-bottom: 10px; }
        .box { border: 1px solid #000; padding: 10px; margin-top: 20px; }
    </style>
</head>
<body>

<h1>Consentiment RGPD - Professorat – Temporada {{ $season }}</h1>
<h2>versió treasury</h2>


<p>
Jo, <strong>{{ $teacher->last_name }} {{ $teacher->first_name }}</strong>, amb correu electrònic
<strong>{{ $teacher->email }}</strong> i  <strong>{{ $teacher->dni }}</strong>, declaro que he llegit i accepto
el tractament de les meves dades personals amb finalitats administratives
i econòmiques.
</p>

<div class="box">
    <p><strong>Dades declarades:</strong></p>
    <ul>
        <li>Nom: {{ $teacher->last_name ?? '—' }}</li>
        <li>Cognoms: {{ $teacher->first_name ?? '—' }}</li>
        <li>Correu-e: {{ $teacher->email ?? '—' }}</li>
        <li>DNI: {{ $teacher->dni ?? '—' }}</li>

    </ul>
</div>
<div class="box">
    <ul>
        {{-- <li>Identificador fiscal: {{ $data['tax_id'] ?? '—' }}</li>
        <li>Compte bancari: {{ isset($data['bank_account']) ? '***' : '—' }}</li> --}}
    </ul>
</div>

<p>
Consentiment acceptat el dia
{{-- <strong>{{ $acceptedAt->format('d/m/Y H:i') }}</strong> --}}
</p>

<p style="margin-top:40px;">
Signatura digital registrada pel sistema.
</p>

</body>
</html>
