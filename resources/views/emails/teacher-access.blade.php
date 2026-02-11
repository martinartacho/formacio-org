<!DOCTYPE html>
<html>
<head>
    <title>{{ __('campus.treasury_mail_title') }}</title>
</head>
<body>
    <h1>{{ __('campus.treasury_mail_title') }}</h1>
    <div>
        <p>
            {{ __('campus.treasury_mail_welcome', ['name' => $teacher->name]) }},
        </p>

        @if ($purpose === 'payments')
            <h2>{{ __('campus.treasury_mail_title_payment') }}</h2>
            <p>
                {{ __('campus.treasury_mail_payment_intro') }}
            </p>

            <p>
                <a href="{{ route('teacher.access.form', [
                    'token' => $token->token,
                    'purpose' => 'payments',
                    'courseCode' => $courseCode
                ]) }}">
                    {{ __('campus.treasury_mail_payment_link') }}
                </a>
            </p>
        @else
            <h2>{{ __('campus.treasury_mail_title_consent') }}</h2>
            <p>
                {{ __('campus.treasury_mail_consent_intro') }}
            </p>

            <p>
                <a href="{{ route('teacher.access.form', [
                    'token' => $token->token,
                    'purpose' => 'consent',
                    'courseCode' => $courseCode
                ]) }}">
                    {{ __('campus.treasury_mail_consent_link') }}
                </a>
            </p>
        @endif

        <p>
            {{ __('campus.treasury_mail_expires') }}
        </p>

        <p>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
