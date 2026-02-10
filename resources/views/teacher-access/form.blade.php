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

    <h1 class="text-2xl font-bold mb-6">Completar dades del professor o professora</h1>
 <p class="text-red-600 text-sm"> {{ $token->token ?? 'token null' }}</p>
 <p class="text-red-600 text-sm"> {{ $purpose  ?? 'purpuse null'}}</p>
 <p class="text-green-600 text-sm"> Teacher:  {{ $teacher  ?? 'Teacher null'}}</p>
 <p class="text-blue-600 text-sm"> Course:  {{ $course->id }} {{ $course  ?? 'Course null'}}</p>

  

    <!-- Verificar si ya se completaron datos básicos -->
 
    @if(isset($purpose) && $purpose === 'consent')
        @if($token->metadata && isset($token->metadata['basic_data_completed']))
            <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-green-700">Les dades personals ja han estat completades</span>
                </div>
                @if(isset($token->metadata['completed_at']))
                    <p class="text-sm text-green-600 mt-1">
                        Completat el: {{ \Carbon\Carbon::parse($token->metadata['completed_at'])->format('d/m/Y H:i') }}
                    </p>
                @endif
                
                <!-- Botón para mostrar/ocultar formulario -->
                <button type="button" 
                        onclick="toggleForm('basic-data-form')"
                        class="mt-3 px-4 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm font-medium">
                    <span id="basic-form-toggle-text">Mostrar formulari per modificar</span>
                </button>
            </div>
        @endif
            
        <!-- Formulario 1: Datos básicos + RGPD -->
        <div class="mb-8 p-4 border rounded-lg bg-gray-50" 
            id="basic-data-form" 
            style="{{ $token->metadata && isset($token->metadata['basic_data_completed']) ? 'display: none;' : '' }}">
            <h2 class="text-xl font-semibold mb-4">1. Dades personals i consentiment RGPD</h2>
            
            <form method="POST" action="{{ route('teacher.access.store', $token->token) }}">
                @csrf

                <div class="mb-4">
                    <input type="text" name="course_id" value="{{ $course->id }}">
                    <label class="block font-medium">Nom:</label>
                    <input type="text" name="first_name" 
                        value="{{ old('first_name', $teacher->first_name ?? '') }}"
                        class="border p-2 w-full" required>
                    @error('first_name')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Cognoms: (2 cognoms)</label>
                    <input type="text" name="last_name" 
                        value="{{ old('last_name', $teacher->last_name ?? '') }}"
                        class="border p-2 w-full" required>
                    @error('last_name')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Correu-e:</label>
                    <input type="email" name="email" 
                        value="{{ old('email', $user->email ?? '') }}"
                        class="border p-2 w-full" required>
                    @error('email')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Telefon:</label>
                    <input type="text" name="phone" 
                        value="{{ old('phone', $teacher->phone ?? $user->phone ?? '') }}"
                        class="border p-2 w-full">
                    @error('phone')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium">DNI:</label>
                        <input type="text" name="dni" 
                            value="{{ old('dni', $teacher->dni ?? '') }}"
                            class="border p-2 w-full mt-1" 
                            placeholder="12345678A">
                        @error('dni')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium"> </label>
                        <div class="space-y-3">
                        {{--  <input type="text" name="teacher" value="{{ $teacher }}" /> --}}
                        </div> 
                    </div>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="consent_rgpd" value="1" required 
                            class="mr-2" {{ old('consent_rgpd') ? 'checked' : '' }}>
                        <span>Accepto el consentiment RGPD</span>
                    </label>
                    @error('consent_rgpd')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    @if($token->metadata && isset($token->metadata['basic_data_completed']))
                        Actualitzar dades personals
                    @else
                        Guardar dades personals
                    @endif
                </button>
            </form>
        </div>
    @endif 
    
    <!-- Verificar si ya se completaron datos de pago -->
   
    @if($token->metadata && isset($token->metadata['payment_data_completed']))
        <h1 class="text-2xl font-semibold mb-4">Control de pepe</h1>
            <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-green-700">Les dades de pagament ja han estat completades</span>
                </div>
                @if(isset($token->metadata['payment_option']))
                    <p class="text-sm text-green-600 mt-1">
                        Opció: {{ $token->metadata['payment_option'] === 'waived_fee' ? 'Renúncia voluntària al cobrament' : 'Cobrament propi' }}
                    </p>
                @endif
                @if(isset($token->metadata['payment_completed_at']))
                    <p class="text-sm text-green-600">
                        Completat el: {{ \Carbon\Carbon::parse($token->metadata['payment_completed_at'])->format('d/m/Y H:i') }}
                    </p>
                @endif
                
                <!-- Botón para mostrar/ocultar formulario -->
                <button type="button" 
                        onclick="toggleForm('payment-data-form')"
                        class="mt-3 px-4 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm font-medium">
                    <span id="payment-form-toggle-text">Mostrar formulari per modificar</span>
                </button>
            </div>
            @endif

    <!-- Formulario 2: Datos de pago -->

    @if(isset($purpose) && $purpose === 'payments')
        <div class="mb-8 p-4 border rounded-lg bg-gray-50" 
            id="payment-data-form" 
            style="{{ $token->metadata && isset($token->metadata['payment_data_completed']) ? 'display: none;' : '' }}">
            <h2 class="text-xl font-semibold mb-4">Dades de pagament</h2>
            
            <!-- Sección de renuncia rápida (siempre visible) -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-medium text-yellow-800 mb-2">Renúncia voluntària al cobrament</h3>
                        <p class="text-sm text-yellow-700 mb-3">
                            Si renuncies voluntàriament al cobrament, pots finalitzar sense completar les dades bancàries.
                        </p>
                        <form method="POST" action="{{ route('teacher.access.store', $token->token) }}" class="inline">
                            @csrf
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            <input type="hidden" name="payment_option" value="waived_fee">
                            <input type="hidden" name="season_id" value="{{ $season->slug ?? '' }}">
                            <input type="hidden" name="course_title" value="{{ $course->title ?? '----' }}">
                            <input type="hidden" name="course_code" value="{{ $course->code ?? '----' }}">
                            <input type="hidden" name="courseasignat-hours" value="{{ $courseasignat->hours_assigned ?? '----' }}">
                            <input type="hidden" name="declaracio_fiscal" value="1">
                            
                            <button type="submit" 
                                    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 font-medium">
                                Finalitzar amb renúncia al cobrament
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Línea separadora -->
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-gray-50 text-gray-500">O selecciona una altra opció</span>
                </div>
            </div>
            
            <!-- Opciones de pago detalladas -->
            <form method="POST" action="{{ route('teacher.access.store', $token->token) }}" id="payment-form">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-3">Opció de pagament:</h3>
                <div class="space-y-3">
                    </div> 
                    <div class="space-y-3">
                        <label class="flex items-start p-3 border rounded hover:bg-blue-50 cursor-pointer">
                            <input type="radio" name="payment_option" value="own_fee" 
                                class="mr-3 mt-1" required 
                                {{ old('payment_option', $token->metadata['payment_option'] ?? '') == 'own_fee' ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Accepto el cobrament</span>
                                <p class="text-sm text-gray-600 mt-1">
                                    Completa les dades bancàries per rebre el pagament directament.
                                </p>
                            </div>
                        </label>
                        
                        <label class="flex items-start p-3 border rounded hover:bg-blue-50 cursor-pointer">
                            <input type="radio" name="payment_option" value="ceded_fee" 
                                class="mr-3 mt-1" 
                                {{ old('payment_option', $token->metadata['payment_option'] ?? '') == 'ceded_fee' ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Derivo el cobrament a una altra persona o entitat.</span>
                                <p class="text-sm text-gray-600 mt-1">
                                    Cedeixo la titularitat amb drets i deures del cobrament a una altra persona o entitat.
                                </p>
                            </div>
                        </label>
                        
                        <label class="flex items-start p-3 border rounded hover:bg-blue-50 cursor-pointer">
                            <input type="radio" name="payment_option" value="waived_fee" 
                                class="mr-3 mt-1" 
                                {{ old('payment_option', $token->metadata['payment_option'] ?? 'waived_fee') == 'waived_fee' ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Renuncio voluntàriament al cobrament</span>
                                <p class="text-sm text-gray-600 mt-1">
                                    Renuncio voluntàriament al cobrament i a les obligacions fiscals derivades.
                                    
                                </p>
                            </div>
                        </label>
                    </div>
                    @error('payment_option')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- NOTA importante -->
                <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm font-medium text-yellow-800">
                        📝 NOTA: La inclusió de les seves dades en l'arxiu de referència és condició indispensable per
                        abonar els seus serveis.
                    </p>
                </div>

                <!-- Detalles de la actividad (siempre visible) -->
                <div class="mb-6 p-4 border rounded bg-gray-100">
                    <div class="font-medium mb-2">📚 Detalls de l'activitat formativa:</div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div class="bg-white p-2 rounded">
                            <div class="font-medium text-gray-500">Curs acadèmic</div>
                            <div class="text-gray-800">[{{ $season->slug ?? '' }}] {{ $season->name ?? '----' }}</div>
                        </div>
                        
                        <div class="bg-white p-2 rounded">
                            <div class="font-medium text-gray-500">Activitat</div>
                            <div class="text-gray-800">[{{ $course->code ?? '--' }}] {{ $course->title ?? '----' }}</div>
                        </div>
                        
                        <div class="bg-white p-2 rounded">
                            <div class="font-medium text-gray-500">Hores assignades</div>
                            <div class="text-gray-800">{{ $courseasignat->hours_assigned ?? '----' }}</div>
                        </div>
                    </div>

                    <input type="hidden" name="season_id" value="{{ $season->slug ?? '' }}">
                    <input type="hidden" name="course_title" value="{{ $course->title ?? '----' }}">
                    <input type="hidden" name="courseasignat-hours" value="{{ $courseasignat->hours_assigned ?? '----' }}">
                </div>

                <!-- Campos de datos fiscales (se muestran dinámicamente) -->
                <div id="payment-fields" class="mt-6">
                    <!-- Solo se muestra si NO es waived_fee -->
                    <div id="bank-data-fields" style="display: none;">
                        <h4 class="text-lg font-medium mb-4 border-b pb-2">💳 Dades bancàries i fiscals</h4>
                    {{--   {{ $teacher }} --}}
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                               
                                <div>
                                    <label class="block font-medium">Identificació fiscal del perceptor:</label>
                                    <span class="block text-sm text-gray-600 mb-1">(Si es diferent del DNI)</span>
                                    <input type="text" name="fiscal_id" 
                                        value="{{ old('fiscal_id', $teacher->metadata['fiscal_id'] ??  $teacher->dni ?? '') }}"
                                        class="border p-2 w-full">
                                    @error('fiscal_id')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block font-medium">Adreça fiscal:</label>
                                <input type="text" name="address" 
                                    value="{{ old('address', $teacher->address ?? '') }}"
                                    class="border p-2 w-full"
                                    placeholder="Carrer, número">
                                @error('address')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium">Codi postal:</label>
                                    <input type="text" name="postal_code" 
                                        value="{{ old('postal_code', $teacher->postal_code ?? '') }}"
                                        class="border p-2 w-full"
                                        placeholder="08401">
                                    @error('postal_code')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block font-medium">Població i Provincia:</label>
                                    <input type="text" name="city" 
                                        value="{{ old('city', $teacher->city ?? '') }}"
                                        class="border p-2 w-full"
                                        placeholder="Granollers, Barcelona">
                                    @error('city')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium">IBAN (24 dígits):</label>
                                    <input type="text" name="iban" 
                                        value="{{ old('iban', $teacher->iban ?? '') }}"
                                        class="border p-2 w-full" 
                                        placeholder="ESXX XXXX XXXX XXXX XXXX XXXX"
                                        pattern="[A-Z]{2}[0-9]{22}"
                                        title="Format: 2 lletres + 22 dígits (ex: ES1234567890123456789012)">
                                    <p class="text-xs text-gray-500 mt-1">Format: ES + 22 dígits</p>
                                    @error('iban')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block font-medium">Titular del compte:</label>
                                    <input type="text" name="bank_titular" 
                                        value="{{ old('bank_titular', $teacher->bank_titular  ?? '') }}"
                                        class="border p-2 w-full"
                                        placeholder="Nom i cognoms del titular">
                                    @error('bank_titular')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Situación fiscal -->
                        <div class="mt-6 p-4 border rounded bg-gray-50">
                            <h5 class="font-medium mb-3">📊 Situació fiscal:</h5>
                            
                            <div class="space-y-2 mb-4" id="fiscal-situation-options">
                                <label class="flex items-center">
                                    <input type="radio" name="fiscal_situation[]" value="autonom" 
                                        class="mr-2 fiscal-option" 
                                        {{ in_array('autonom', old('fiscal_situation', [])) ? 'checked' : '' }}
                                        onchange="handleFiscalSituationChange()">
                                    <span>Autònom/a</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="radio" name="fiscal_situation[]" value="treballador" 
                                        class="mr-2 fiscal-option" 
                                        {{ in_array('treballador', old('fiscal_situation', [])) ? 'checked' : '' }}
                                        onchange="handleFiscalSituationChange()">
                                    <span>Treballador/a per compte alié</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="radio" name="fiscal_situation[]" value="jubilat" 
                                        class="mr-2 fiscal-option" 
                                        {{ in_array('jubilat', old('fiscal_situation', [])) ? 'checked' : '' }}
                                        onchange="handleFiscalSituationChange()">
                                    <span>Jubilat/ada</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="radio" name="fiscal_situation[]" value="jubilat_especial" 
                                        class="mr-2 fiscal-option" 
                                        {{ in_array('jubilat_especial', old('fiscal_situation', [])) ? 'checked' : '' }}
                                        onchange="handleFiscalSituationChange()">
                                    <span>Jubilat/ada amb conveni especial amb la Seguretat Social o amb jubilació activa</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="radio" name="fiscal_situation[]" value="altres" 
                                        class="mr-2 fiscal-option" 
                                        {{ in_array('altres', old('fiscal_situation', [])) ? 'checked' : '' }}
                                        onchange="handleFiscalSituationChange()">
                                    <span>Altres</span>
                                </label>
                            </div>
                            
                            <!-- Camp per a "Altres" (es mostra nomes si es selecciona) -->
                            <div id="altres-fiscal-situation" style="display: none;" class="mt-3">
                                <label class="block font-medium mb-2">Especifica la teva situació fiscal:</label>
                                <input type="text" name="fiscal_situation_other" 
                                    value="{{ old('fiscal_situation_other') }}"
                                    class="border p-2 w-full"
                                    placeholder="Descriu la teva situació fiscal...">
                            </div>
                            
                            @error('fiscal_situation')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror

                            
                        </div>
                                            
{{--                         <div id="observacions_own_fee" style="display: none;" class="mt-3">>
                            <label class="block font-medium">Observacions:</label>
                            <input type="text" name="observacions_own_fee" 
                                value="{{ old('observacions_own_fee', $teacher->observacions_own_fee ?? '') }}"
                                class="border p-2 w-full"
                                placeholder="Indica altres consideracions a tenir en compte">
                            @error('observacions_own_fee')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div> --}}

                        <div id="observacions_ceded_fee" style="display: none;" class="mt-3">>
                            <label class="block font-medium">Observacions:</label>
                            <input type="text" name="observacions_ceded_fee" 
                                value="{{ old('observacions_ceded_fee', $teacher->observacions_ceded_fee ?? '') }}"
                                class="border p-2 w-full"
                                placeholder="Pagament derivat a honoraris, altres institucions, lloguer d'espais, etc">
                            @error('observacions')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                        
                    </div>
                </div>

                <!-- Declaración fiscal (fuera de divs ocultos) -->
                <div id="declaracio-fiscal-container" class="mt-4 p-3 border rounded bg-white">
                    <label class="flex items-start">
                        <input type="checkbox" name="declaracio_fiscal" value="1" required 
                            class="mr-2 mt-1">
                        <span class="text-sm">
                            Declaro que soc coneixedor/a de la fiscalitat corresponent als ingressos previstos i soc
                            conscient de les responsabilitats que comporta.
                        </span>
                    </label>
                    @error('declaracio_fiscal')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Autorización tratamiento datos -->
                <div class="mt-6 p-4 border rounded bg-blue-50">
                    <h4 class="font-medium mb-2">🔒 Autorització tractament de dades:</h4>
                    <div class="flex items-start mb-3">
                        <input type="checkbox" id="autoritzacio-dades" name="autoritzacio_dades" value="1" required
                            class="mr-2 mt-1" {{ old('autoritzacio_dades') ? 'checked' : '' }}>
                        <label for="autoritzacio-dades" class="text-sm">
                            Sí autoritzo la inclusió de les meves dades en el fitxer «professorat» de la UPG.
                        </label>
                    </div>
                    @error('autoritzacio_dades')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                    
                    <div class="text-xs text-gray-600 max-h-32 overflow-y-auto p-3 bg-white border rounded text-justify">
                        <p class="leading-relaxed">
                            La Universitat Popular de Granollers informa que, en compliment de la legislació vigent (Llei Orgànica
                            3/2018, de 5 de desembre, de Protecció de dades personals i garantia dels drets digitals, i la normativa de
                            desenvolupament, així com, si escau, amb el Reglament (UE) 2016/679 del Parlament Europeu i del
                            Consell, de 27 d'abril de 2016, relatiu a la protecció de les persones físiques pel que fa al tractament de
                            dades personals i a la lliure circulació d'aquestes dades i pel qual es deroga la Directiva 95/46/CE) les
                            seves dades seran incloses al fitxer responsabilitat de l'ASSOCIACIÓ IMPULSORA DE
                            L'EDUCACIO POPULAR (AIEP) -Universitat Popular de Granollers- anomenat "Professors" per a la
                            tramitació administrativa relacionada amb el pagament dels seus serveis, gestionat pel personal voluntari
                            responsable de les tasques administratives relacionades amb el pagament dels mateixos.
                            Si ho desitja pot exercir els drets d'accés, rectificació, cancel·lació i oposició de les seves dades dirigint-se
                            a l'ASSOCIACIÓ IMPULSORA DE L'EDUCACIO POPULAR ( AIEP), C/ Mare de Montserrat, 36 Edifici
                            Roca Umbert, 08401 – GRANOLLERS, o bé escrivint a l'adreça de correu electrònic secretaria@upg.cat
                            adjuntant una còpia del seu DNI.
                        </p>
                    </div>
                </div>

                <!-- Botón de envío principal -->
                <button type="submit" class="mt-6 w-full bg-green-600 text-white px-4 py-3 rounded hover:bg-green-700 font-medium text-lg">
                    @if($token->metadata && isset($token->metadata['payment_data_completed']))
                        ✅ Actualitzar dades de pagament
                    @else
                        ✅ Enviar dades de pagament
                    @endif
                </button>
            </form>
        </div>
    @endif

</div>

<script>
// Función para manejar cambios en situación fiscal
function handleFiscalSituationChange() {
    const altresOption = document.querySelector('input[name="fiscal_situation[]"][value="altres"]');
    const altresField = document.getElementById('altres-fiscal-situation');
    
    if (altresOption && altresField) {
        if (altresOption.checked) {
            altresField.style.display = 'block';
        } else {
            altresField.style.display = 'none';
        }
    }
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    handleFiscalSituationChange();
    
    // Validación personalizada del IBAN
    const ibanInput = document.querySelector('input[name="iban"]');
    if (ibanInput) {
        ibanInput.addEventListener('blur', function() {
            const iban = this.value.replace(/\s/g, '').toUpperCase();
            
            if (iban.length > 0) {
                // Validar formato básico
                if (iban.length !== 24) {
                    showIbanError('L\'IBAN ha de tenir exactament 24 caràcters');
                } else if (!iban.startsWith('ES')) {
                    showIbanError('L\'IBAN ha de començar amb "ES" per a comptes espanyols');
                } else if (!/^[A-Z]{2}[0-9]{22}$/.test(iban)) {
                    showIbanError('Format incorrecte. Ha de ser: 2 lletres + 22 dígits (ex: ES1234567890123456789012)');
                } else {
                    hideIbanError();
                }
            }
        });
    }
    
    function showIbanError(message) {
        let errorDiv = document.getElementById('iban-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'iban-error';
            errorDiv.className = 'text-red-600 text-sm mt-1';
            ibanInput.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        ibanInput.classList.add('border-red-500');
    }
    
    function hideIbanError() {
        const errorDiv = document.getElementById('iban-error');
        if (errorDiv) {
            errorDiv.remove();
        }
        ibanInput.classList.remove('border-red-500');
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos principales
    const paymentOptions = document.querySelectorAll('input[name="payment_option"]');
    const bankDataFields = document.getElementById('bank-data-fields');
    const waivedFeeFields = document.getElementById('waived-fee-fields');
    
    // Función para manejar cambios en opciones de pago
    function handlePaymentOptionChange() {
        const selectedOption = document.querySelector('input[name="payment_option"]:checked');
        
        if (!selectedOption) return;
        
        const optionValue = selectedOption.value;
        
        // Ocultar todos los campos primero
        if (bankDataFields) bankDataFields.style.display = 'none';
        if (waivedFeeFields) waivedFeeFields.style.display = 'none';
       // if (cededFeeFields) cededFeeFields.style.display = 'none';
        
        // Resetear requeridos y hacer visibles los checkboxes de declaración
        resetAllFields();
        makeDeclarationCheckboxesVisible();
        
        // Mostrar campos según opción seleccionada
        if (optionValue === 'own_fee') {
            if (bankDataFields) bankDataFields.style.display = 'block';
            setFieldsRequired(['fiscal_id', 'address', 'postal_code', 'city', 'iban', 'bank_titular', 'declaracio_fiscal', 'autoritzacio_dades'], true);
           //  setFieldsRequired(['titol'], false);
        } 
         else if (optionValue === 'ceded_fee') {
            if (bankDataFields) bankDataFields.style.display = 'block';
            if (cededFeeFields) cededFeeFields.style.display = 'block';
            setFieldsRequired(['fiscal_id', 'address', 'postal_code', 'city', 'iban', 'bank_titular', 'titol', 'declaracio_fiscal', 'autoritzacio_dades', 'observacions_ceded_fee'], true);
        } 
        else if (optionValue === 'waived_fee') {
            if (waivedFeeFields) waivedFeeFields.style.display = 'block';
            setFieldsRequired([ 'declaracio_fiscal', 'autoritzacio_dades'], true);
            setFieldsRequired(['fiscal_id', 'address', 'postal_code', 'city', 'iban', 'bank_titular'], false);
        }
    }

    // Función para hacer visibles los checkboxes de declaración
    function makeDeclarationCheckboxesVisible() {
        const declarationCheckboxes = document.querySelectorAll('[name="declaracio_fiscal"], [name="autoritzacio_dades"]');
        declarationCheckboxes.forEach(checkbox => {
            // Asegurar que siempre sean visibles
            checkbox.style.visibility = 'visible';
            checkbox.style.position = 'relative';
            checkbox.style.opacity = '1';
        });
    }
    
    // Función para resetear todos los campos
    function resetAllFields() {
        const allFields = ['dni', 'fiscal_id', 'address', 'postal_code', 'city', 'iban', 'bank_titular', 'titol', 'declaracio_fiscal', 'autoritzacio_dades'];
        allFields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.required = false;
            }
        });
    }
    
    // Función para establecer campos como requeridos
    function setFieldsRequired(fieldNames, required) {
        fieldNames.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.required = required;
                // También marcar visualmente si es requerido
                const label = field.closest('div')?.querySelector('label');
                if (label) {
                    if (required) {
                        label.classList.add('required-field');
                    } else {
                        label.classList.remove('required-field');
                    }
                }
            }
        });
    }
    
    // Escuchar cambios en las opciones de pago del formulario principal
    if (paymentOptions.length > 0) {
        paymentOptions.forEach(option => {
            option.addEventListener('change', handlePaymentOptionChange);
        });
        
        // Inicializar según opción seleccionada
        const initialOption = document.querySelector('input[name="payment_option"]:checked');
        if (initialOption) {
            handlePaymentOptionChange();
        } else {
            // Por defecto, mostrar campos para waived_fee
            const defaultOption = document.querySelector('input[name="payment_option"][value="waived_fee"]');
            if (defaultOption) {
                defaultOption.checked = true;
                handlePaymentOptionChange();
            }
        }
    }
    
    // Estilo para campos requeridos
    const style = document.createElement('style');
    style.textContent = `
        .required-field::after {
            content: ' *';
            color: #ef4444;
        }
        input:required {
            border-left-color: #3b82f6;
        }
    `;
    document.head.appendChild(style);
    
    // Restaurar valores antiguos si hay error de validación
    @if(old('payment_option'))
        const oldOption = document.querySelector(`input[name="payment_option"][value="{{ old('payment_option') }}"]`);
        if (oldOption) {
            oldOption.checked = true;
            handlePaymentOptionChange();
        }
    @endif
    
    // Función para mostrar/ocultar formularios completados
    window.toggleForm = function(formId) {
        const form = document.getElementById(formId);
        const toggleText = document.getElementById(formId + '-toggle-text');
        
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            if (toggleText) toggleText.textContent = 'Amagar formulari';
            
            // Re-inicializar campos si es el formulario de pago
            if (formId === 'payment-data-form') {
                setTimeout(handlePaymentOptionChange, 100);
            }
        } else {
            form.style.display = 'none';
            if (toggleText) toggleText.textContent = 'Mostrar formulari per modificar';
        }
    };
});
</script>
@endsection