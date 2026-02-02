{{-- @extends('campus.shared.layoutobert') --}}

@extends('campus.shared.layout')


@section('title', __('campus.treasury'))
@section('subtitle', __('campus.treasury_mail_title'))


@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Completar dades del professor o professora</h1>

    <form method="POST" action="{{ route('teacher.access.store', $token->token) }}">
        @csrf

        
        <div class="mb-4">
            <label class="block font-medium">Nom:</label>
            <input type="text" name="first_name" 
                   value="{{ old('first_name', $teacher->first_name ?? '') }}"
                   class="border p-2 w-full" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Cognoms: (2 cognoms)</label>
            <input type="text" name="last_name" 
                   value="{{ old('last_name', $teacher->last_name ?? '') }}"
                   class="border p-2 w-full" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Correu-e:</label>
            <input type="text" name="email" 
                   value="{{ old('email', $user->email ?? '') }}"
                   class="border p-2 w-full" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Telefon:</label>
            <input type="text" name="phone" 
                   value="{{ old('phone', $user->phone ?? '') }}"
                   class="border p-2 w-full">
        </div>

        <div class="mb-4">
            <label>
                <input type="checkbox" name="consent_rgpd" value="1" required>
                Accepto el consentiment RGPD
            </label>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Enviar
        </button>
    </form>

    <form method="POST" action="{{ route('teacher.access.store', $token->token) }}">
        @csrf

        <div class="mb-4">

            <h2 class="text-2xl font-bold mb-6">Dades de pagament</h2>
                <label class="block font-medium"> <input type="checkbox" name="payment_option" value="own_fee"> Faré el cobrament</label>
                <label class="block font-medium"> <input type="checkbox" name="payment_option" value="ceded_fee"> Cedeixo la titularitat amb drets i deures del cobrament a una altra persona o entitat.</label>
                <label class="block font-medium"> <input type="checkbox" name="payment_option" value="waived_fee" default> Renuncio voluntàriament al cobrament i a les obligacions fiscals derivades.</label>
            <br>

            <label>
                NOTA: La inclusió de les seves dades en l’arxiu de referència és condició indispensable per
abonar els seus serveis.
            </label>
            
        </div>

        <div class="mb-4">
            <hr>
            <code>
            {{ $courseasignat }}
            </code>
            <hr>
            
            <label class="block font-medium">
                Curs acadèmic:
                <span class="font-normal text-gray-700">
                    [{{ $season->slug ?? '' }}] {{ $season->name ?? '----' }}
                </span>
                ·
                Nom de l’activitat formativa:
                <span class="font-normal text-gray-700">
                    {{ $course->title ?? '----' }}
                </span>
                ·
                Hores assignades:
                <span class="font-normal text-gray-700">
                    {{ $courseasignat->hours_assigned ?? '----' }}
                </span>

            </label>

            <input type="hidden" name="season_id" value="{{ $season->slug ?? '' }}">
            <input type="hidden" name="course_title" value="{{ $course->title ?? '----' }}">
            <input type="hidden" name="courseasignat-hours" value=" {{ $courseasignat->hours ?? '----' }}">

        </div>

        <div id="payment-fields" style="display: none;">
            <label class="block font-medium">Si, renuncio al cobrament les dades han de ser les referents al perceptor:</label>
            <label class="block font-medium">Renuncio al cobrament de l’import corresponent als meus serveis a favor de:</label>
                <input type="text" name="titol" class="border p-2 w-full">
            <div class="mb-4">
                <label class="block font-medium">DNI:</label>
                <input type="text" name="dni" 
                    value="{{ old('dni', $teacher->dni ?? '') }}"
                    class="border p-2 w-full">
                <label class="block font-medium"> Identificació fiscal: <span class="font-normal text-gray-700">Si es diferent del DNI
                    </span></label>
                <input type="text" name="fiscal_id" 
                    value="{{ old('fiscal_id', $teacher->fiscal_id ?? '') }}"
                    class="border p-2 w-full">

            </div>

            
            <div class="mb-4">
                <label class="block font-medium">Adreça fiscal:</label>
                <input type="text" name="address" class="border p-2 w-full">
            </div>

            <div class="mb-4">
                <label class="block font-medium">Codi postal:</label>
                <input type="text" name="postal_code" class="border p-2 w-full">
            </div>

            <div class="mb-4">
                <label class="block font-medium">Població i Provincia:</label>
                <input type="text" name="city" class="border p-2 w-full">
            </div>

            <div class="mb-4">
                <label class="block font-medium">IBAN (24 dígits):</label>
                <input type="text" name="iban" class="border p-2 w-full">
            </div>

            <div class="mb-4">
                <label class="block font-medium">Titular del compte:</label>
                <input type="text" name="bank_swift" class="border p-2 w-full">
            </div>

          
            <div class="mb-4">
                <label class="block font-medium">Situació fiscal::</label>
                <label class="block font-medium"> <input type="checkbox" name="fiscal_situation"> Autònom/a</label>
                <label class="block font-medium"> <input type="checkbox" name="fiscal_situation"> Treballador/a per compte alié</label>
                <label class="block font-medium"> <input type="checkbox" name="fiscal_situation"> Jubilat/ada</label>
                <label class="block font-medium"> <input type="checkbox" name="fiscal_situation"> Jubilat/ada amb conveni especial amb la Seguretat Social o amb jubilació activa</label>
                
                <label class="block font-medium"> <input type="checkbox" name="titol"> Declaro que soc coneixedor/a de la fiscalitat corresponent als ingressos previstos i soc
conscient de les responsabilitats que comporta.</label>     
            </div>



            <div class="mb-4">
                <p class="block font-medium">Sí autoritzo la inclusió de les meves dades en el fitxer «professorat» de la UPG.:</p>
                <p class="block font-medium cursive"> La Universitat Popular de Granollers informa que, en compliment de la legislació vigent (Llei Orgànica
3/2018, de 5 de desembre, de Protecció de dades personals i garantia dels drets digitals, i la normativa de
desenvolupament, així com, si escau, amb el Reglament (UE) 2016/679 del Parlament Europeu i del
Consell, de 27 d'abril de 2016, relatiu a la protecció de les persones físiques pel que fa al tractament de
dades personals i a la lliure circulació d'aquestes dades i pel qual es deroga la Directiva 95/46/CE) les
seves dades seran incloses al fitxer responsabilitat de l’ASSOCIACIÓ IMPULSORA DE
L'EDUCACIO POPULAR (AIEP) -Universitat Popular de Granollers- anomenat ”Professors” per a la
tramitació administrativa relacionada amb el pagament dels seus serveis, gestionat pel personal voluntari
responsable de les tasques administratives relacionades amb el pagament dels mateixos.
Si ho desitja pot exercir els drets d’accés, rectificació, cancel·lació i oposició de les seves dades dirigint-se
a l’ASSOCIACIÓ IMPULSORA DE L'EDUCACIO POPULAR ( AIEP), C/ Mare de Montserrat, 36 Edifici
Roca Umbert, 08401 – GRANOLLERS, o bé escrivint a l’adreça de correu electrònic secretaria@upg.cat
adjuntant una còpia del seu DNI.</p>
            </div>


        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Enviar
        </button>
    </form>
</div>

<script>
document.querySelector('input[name="payment_option"]').addEventListener('change', function() {
    document.getElementById('payment-fields').style.display = this.checked ? 'block' : 'none';
});
</script>

{{-- Debug info (solo desarrollo) --}}
@if(env('APP_DEBUG'))
<div class="mt-8 p-4 bg-gray-100">
    <h3 class="font-bold">Debug Info:</h3>
    <p>Token: {{ $token->token }}</p>
    <p>User ID: {{ $user->id ?? 'N/A' }}</p>
    <p>Teacher ID: {{ $teacher->id ?? 'N/A' }}</p>
    <p>Teacher First Name: {{ $teacher->first_name ?? 'N/A' }}</p>
    <p>User Email: {{ $user->email ?? 'N/A' }}</p>
</div>
@endif
@endsection