<!DOCTYPE html>
<html>
<head>
    <title>{{ __('Dades del professorat de la UPG') }}</title>
</head>
<body>
    <h1>{{ __('Dades del professorat de la UPG') }}</h1>
    <div>
        <p>
            {{ __('Hola', ['name' => $teacher->name]) }},
        </p>

        @if ($purpose === 'payments')
           
            <p>
                {{ __('Benvingut/a dades professorat UPG') }}
            </p>

            <p>
                <a href="{{ route('teacher.access.form', [
                    'token' => $token->token,
                    'purpose' => 'payments',
                    'courseCode' => $courseCode
                ]) }}">
                    {{ __('Formulari dades professorat UPG') }}
                </a>
            </p>
        @else
            
            <p>
                 {{ __('Benvingut/a dades professorat UPG') }}
            </p>

            <p>
                <a href="{{ route('teacher.access.form', [
                    'token' => $token->token,
                    'purpose' => 'consent',
                    'courseCode' => $courseCode
                ]) }}">
                    {{ __('Formulari dades professorat UPG') }}
                </a>
            </p>
        @endif

        <p>
            {{ __('Aquest enllaç caduca en 7 dies.') }}
        </p>

        <p>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
