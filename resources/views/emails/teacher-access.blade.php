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

        Pots accedir al formulari per completar els consentiments mitjançant aquest enllaç:

        <a href="{{ route('teacher.access.form', $token->token) }}">Accedir al formulari</a>
        

        <code>{{ route('teacher.access.form', $token->token) }}</code>

        Aquest enllaç caduca en 7 dies.

        Gràcies.

    </p>

    <p>
    {{ config('app.name') }}  
    </p>
    </div>
</body>
</html>
