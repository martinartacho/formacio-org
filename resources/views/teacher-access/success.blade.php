@extends('campus.shared.layout')

@section('title', __('campus.treasury'))
@section('subtitle', __('campus.treasury_mail_title'))

@section('content')
<div class="container mx-auto py-8">
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Mensaje de éxito -->
    <div class="mb-8 p-6 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center mb-4">
            <svg class="w-8 h-8 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h1 class="text-2xl font-bold text-green-800">Dades registrades correctament</h1>
                <p class="text-green-700 mt-1">{{ $message ?? 'Les dades s\'han registrat correctament.' }}</p>
            </div>
        </div>
        
        <!-- Información del consentimiento -->
        @if($latestConsent)
            <div class="mt-4 p-4 bg-white border border-green-100 rounded">
                <h3 class="font-medium text-green-800 mb-2">📄 Consentiment RGPD</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Consentiment actualitzat el {{ \Carbon\Carbon::parse($latestConsent->updated_at)->format('d/m/Y H:i') }}
                </p>
            </div>
        @endif
        
        <!-- Información del pago -->
        @if($token->metadata && isset($token->metadata['payment_data_completed']))
            <div class="mt-4 p-4 bg-white border border-green-100 rounded">
                <h3 class="font-medium text-green-800 mb-2">💰 Dades de pagament</h3>
                <p class="text-sm text-gray-600">
                    @if(isset($token->metadata['payment_option']))
                        @php
                            $paymentOptions = [
                                'own_fee' => 'Faré el cobrament',
                                'ceded_fee' => 'Cedeixo la titularitat',
                                'waived_fee' => 'Renuncio voluntàriament al cobrament'
                            ];
                        @endphp
                        Opció seleccionada: <strong>{{ $paymentOptions[$token->metadata['payment_option']] ?? $token->metadata['payment_option'] }}</strong>
                    @endif
                    @if(isset($token->metadata['payment_completed_at']))
                        <br>Completat el: {{ \Carbon\Carbon::parse($token->metadata['payment_completed_at'])->format('d/m/Y H:i') }}
                    @endif
                </p>
            </div>
        @endif
    </div>
    
    <!-- Enlaces de acción -->
    <div class="mt-8 flex flex-col sm:flex-row gap-4">

        {{--  Si encara no has omplert el formulari, torna el professor o sencillament tonar al formulari --}}
        {{-- @if($teacher)  
            <a href="{{ url('/') }}/teacher-access/28a35613-2aee-495d-bf3c-d38711d3a374" 
               class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white rounded hover:bg-gray-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
               Tornar al formulari
            </a>
        @endif --}}
       
        {{--  Si Ja ha omplert el formulariPot fer login --}}
        <a href="{{ url('/') }}" 
           class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Entrar al campus
        </a>

         @if($teacher)
            <div class="mt-4">
                <p class="text-gray-600 mb-2">Si no recordes la teva contrasenya:</p>
                <a href="{{ route('password.request') }}" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Recuperar contrasenya
                </a>
            </div>
        @endif
        
    </div>
    
    <!-- Resumen de datos -->
    @if($teacher)
        <div class="mt-8 p-6 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">📋 Resum de les teves dades</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Dades personals</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li><strong>Nom:</strong> {{ $teacher->first_name }}</li>
                        <li><strong>Cognoms:</strong> {{ $teacher->last_name }}</li>
                        <li><strong>Email:</strong> {{ $teacher->email }}</li>
                        <li><strong>Telèfon:</strong> {{ $teacher->phone ?? 'No especificat' }}</li>
                    </ul>
                </div>
                
                @if($token->metadata && isset($token->metadata['payment_option']))
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Dades de pagament</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>
                                <strong>Estat:</strong> 
                                <span class="px-2 py-1 rounded text-xs {{ $token->metadata['payment_option'] === 'waived_fee' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $token->metadata['payment_option'] === 'waived_fee' ? 'Renúncia' : 'Actiu' }}
                                </span>
                            </li>
                            @if(isset($token->metadata['payment_completed_at']))
                                <li><strong>Data:</strong> {{ \Carbon\Carbon::parse($token->metadata['payment_completed_at'])->format('d/m/Y') }}</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection