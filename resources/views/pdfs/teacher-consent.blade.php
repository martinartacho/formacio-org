<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        .box { border: 1px solid #000; padding: 10px; margin-top: 20px; }
        .small { font-size: 10px; color: #555; }
    </style>
</head>
<body>

<h1>Consentiment RGPD – Professorat</h1>

<h2>versió pdfs</h2>
<p><strong>Professor/a:</strong> {{ $teacher->name }}</p>
<p><strong>Email:</strong> {{ $teacher->email }}</p>
<p><strong>Temporada:</strong> {{ $season->name }} ({{ $season->slug }})</p>
<p><strong>Data acceptació:</strong> {{ $acceptedAt->format('d/m/Y H:i') }}</p>

@if ($delegatedBy)
    <div class="box">
        <p><strong>⚠ Consentiment delegat</strong></p>
        <p><strong>Realitzat per:</strong> {{ $delegatedBy->name }} ({{ $delegatedBy->email }})</p>
        <p><strong>Motiu:</strong> {{ $delegatedReason }}</p>
    </div>
@endif

<div class="box">
    <p>
        Mitjançant aquest document es deixa constància del consentiment
        per al tractament de dades personals segons la normativa RGPD vigent.
    </p>
</div>

<p class="small">
    Checksum document: {{ $checksum }}<br>
    Document generat automàticament pel sistema.
</p>

</body>
</html>
