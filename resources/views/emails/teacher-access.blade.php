<!DOCTYPE html>
<html>
<head>
    <title>{{ __('Dades del professorat de la UPG') }}</title>
</head>
<body>
    <h1>{{ __('Dades del professorat de la UPG') }}</h1>
    <div>
        <p>
            {{ __('Hola', ['name' => $teacher->first_name .' '.$teacher->last_name])  ??  $user->name }},
        </p>

        @if ($purpose === 'payments')
           
            <p>
                {{ __('Benvingut docent de la Universitat Popular de Granollers - UPG') }}
                {{ __('Per tal de gestionar correctament la formació que imparteixes aquest curs, et donem seguidament l´enllaç al formulari per a completar les teves dades.') }}
                Consideracions:
                {{ __('Rebràs un missatge com aquest si imparteix més d´un curs.') }}
                {{ __('A cada curs impartit, pots triar diferents modalitats de cobrament, tria la que consideris.') }}
                {{ __('Alguna observació ...') }}
            </p>

            <p>
                <a href="{{ route('teacher.access.form', [
                    'token' => $token->token,
                    'purpose' => 'payments',
                    'courseCode' => $courseCode
                ]) }}">
                    {{ __('Formulari dades professorat UPG') }}
                </a>
                
                <br>
                <span>{{ __('o copi i enganxa aquest enllaç al teu navegador:') }}</span>
                <code>
                    http://localhost:8000/teacher.access.form?token={{ $token->token }}&purpose=payments&courseCode={{ $courseCode }}
                </code>
            </p>
        @else
            
            <p>
                {{ __('Benvingut docent de la Universitat Popular de Granollers - UPG') }}
                {{ __('Per tal de gestionar correctament la formació que imparteixes aquest curs, et donem seguidament l´enllaç al formulari per a completar les teves dades.') }}
                Consideracions:
                {{ __('Rebràs un missatge com aquest si imparteix més d´un curs.') }}
                {{ __('A cada curs impartit, pots triar diferents modalitats de cobrament, tria la que consideris.') }}
                {{ __('Alguna observació ...') }}

            </p>

            <p>
                <a href="{{ route('teacher.access.form', [
                    'token' => $token->token,
                    'purpose' => 'consent',
                    'courseCode' => $courseCode
                ]) }}">
                    {{ __('Formulari dades professorat UPG') }}
                </a>
                <br>
                <span>{{ __('o copi i enganxa aquest enllaç al teu navegador:') }}</span>
                <code>
                    http://localhost:8000/teacher.access.form?token={{ $token->token }}&purpose=consent&courseCode={{ $courseCode }}
                </code>
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
