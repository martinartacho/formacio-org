<!DOCTYPE html>
<html>
<head>
    <title>{{ __('campus.treasury_mail_title') }}</title>
</head>
<body>
    <h1>{{ __('campus.treasury_mail_title') }}</h1>
    <div>
      
    
    

    <p>
    Hola {{ $teacher->name }},
</p>

@if ($purpose === 'payments')
    <h2>{{ __('campus.treasury_mail_title_payment') }}</h2>
    <p>
        Pots accedir al formulari per completar les dades econòmiques mitjançant aquest enllaç:
    </p>

    <p>
        <a href="{{ route('teacher.access.form', [
            'token' => $token->token,
            'purpose' => 'payments',
            'courseCode' => $courseCode
        ]) }}">
            Accedir al formulari de pagaments
        </a>
        
        
    </p>
 
    
    
@else
    <h2>{{ __('campus.treasury_mail_title_consent') }}</h2>
    <p>
        Pots accedir al formulari per completar els consentiments mitjançant aquest enllaç:
    </p>

    <p>
        <a href="{{ route('teacher.access.form', [
            'token' => $token->token,
            'purpose' => 'consent',
            'courseCode' => $courseCode
        ]) }}">
            Accedir al formulari de consentiments
        </a>
    </p>
@endif


<p>
    Aquest enllaç caduca en 7 dies.
</p>

<p>
    {{ config('app.name') }}
</p>
    </div>
</body>
</html>
