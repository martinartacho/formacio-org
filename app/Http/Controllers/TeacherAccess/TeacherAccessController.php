<?php

namespace App\Http\Controllers\TeacherAccess;

use App\Http\Controllers\Controller;
use App\Models\TeacherAccessToken;
use App\Models\CampusTeacher;
use App\Models\CampusTeacherPayment;
use App\Models\ConsentHistory;
use App\Models\CampusSeason;
use App\Models\CampusCourse;
use App\Models\CampusCourseTeacher;
use App\Models\TreasuryData;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherAccessController extends Controller
{
    public function show(string $token)
    {
        \Log::info('=== TEACHER ACCESS START ===');
        \Log::info('Token recibido:', ['token' => $token]);
        
        // 1. Buscar season actual
        $season = CampusSeason::where('is_current', true)->first();       
       
        // 2. Buscar el token
        $accessToken = TeacherAccessToken::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$accessToken) {
            \Log::error('Token no encontrado o expirado:', ['token' => $token]);
            abort(404, 'Enlace no válido o expirado');
        }
        
        \Log::info('Token encontrado:', [
            'id' => $accessToken->id,
            'teacher_id' => $accessToken->teacher_id,
            'expires_at' => $accessToken->expires_at,
            'used_at' => $accessToken->used_at
        ]);

        // 3. Buscar el usuario
        $user = User::find($accessToken->teacher_id);
        
        if (!$user) {
            \Log::error('Usuario no encontrado:', ['teacher_id' => $accessToken->teacher_id]);
            abort(404, 'Usuario no encontrado');
        }
        
        \Log::info('Usuario encontrado:', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone
        ]);

        // 4. Buscar el profesor relacionado
        $teacher = CampusTeacher::where('user_id', $user->id)->first();
        
        // Si no existe, creamos un objeto vacío
        if (!$teacher) {
            \Log::warning('CampusTeacher no encontrado, se usará objeto vacío');
            $teacher = new CampusTeacher(['user_id' => $user->id]);
        } else {
            \Log::info('CampusTeacher encontrado:', [
                'teacher_id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'phone' => $teacher->phone
            ]);
        }

        // 5. Buscar asignación de curso
        $courseasignat = CampusCourseTeacher::where('teacher_id', $teacher->id ?? null)->first();
        
        $course = null;
        if ($courseasignat && $courseasignat->course_id) {
            $course = CampusCourse::find($courseasignat->course_id);
        }

        \Log::info('=== TEACHER ACCESS END ===');
      //  dd($teacher);

        return view('teacher-access.form', [
            'token' => $accessToken,
            'user' => $user,
            'season' => $season,
            'teacher' => $teacher,
            'course' => $course,
            'courseasignat' => $courseasignat
        ]);
    }

    public function store(Request $request, string $token)
    {
        \Log::info('=== TEACHER ACCESS STORE ===');
        DB::beginTransaction();
        \Log::info('Token recibido:', ['token' => $token]);
        
        try {
            // Validar token
            $accessToken = TeacherAccessToken::where('token', $token)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->firstOrFail();
            
            $user = User::findOrFail($accessToken->teacher_id);
            \Log::info('User encontrado:', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
           //  dd( "recibimos : $request{'consent_rgpd'}", $request);


            // Determinar qué formulario se está enviando
            if ($request->has('consent_rgpd')) {
                // Formulario 1: Datos básicos + RGPD
                return $this->handleBasicDataForm($request, $user, $accessToken);
            } elseif ($request->has('payment_option')) {
                // Formulario 2: Datos de pago
                return $this->handlePaymentDataForm($request, $user, $accessToken);
            } else {
                throw new \Exception('Formulario no válido');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar datos del profesor: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al guardar los datos: ' . $e->getMessage()]);
        }
    }
    
    /*      Versió anterios funcional sense pdf
    private function handleBasicDataForm(Request $request, User $user, TeacherAccessToken $accessToken)
    {
        // Validar datos básicos
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:200',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'consent_rgpd' => 'required|accepted',
        ]);
        

        // Actualizar usuario
        $user->update([
            'email' => $validated['email'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        ]);
        
        // Buscar o crear profesor
        $teacher = CampusTeacher::where('user_id', $user->id)->first();
        
        if ($teacher) {
            // Actualizar profesor existente
            $teacher->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);
        } else {
            // Crear nuevo profesor
            $teacher = CampusTeacher::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'teacher_code' => 'TCH' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
        }
        
        // Registrar consentimiento RGPD
        $seasonSlug = CampusSeason::where('is_current', true)->first()->slug ?? date('Y');
        $documentName = 'consent-rgpd-' . $teacher->id . '-' . now()->format('Y-m-d') . '.pdf';
        
        // Buscar consentimiento existente
        $existingConsent = ConsentHistory::where('teacher_id', $teacher->id)
            ->where('season', $seasonSlug)
            ->first();
        
        if ($existingConsent) {
            // Actualizar consentimiento existente
            $existingConsent->update([
                'document_path' => 'consents/' . $documentName,
                'accepted_at' => now(),
                'checksum' => md5(serialize($validated) . now()->timestamp),
            ]);
            
            Log::info('Consentimiento actualizado:', [
                'consent_id' => $existingConsent->id,
                'teacher_id' => $teacher->id,
                'season' => $seasonSlug
            ]);
        } else {
            // Crear nuevo consentimiento
            ConsentHistory::create([
                'teacher_id' => $teacher->id,
                'season' => $seasonSlug,
                'document_path' => 'consents/' . $documentName,
                'accepted_at' => now(),
                'checksum' => md5(serialize($validated) . now()->timestamp),
            ]);
            
            Log::info('Consentimiento creado:', [
                'teacher_id' => $teacher->id,
                'season' => $seasonSlug
            ]);
        }
        
        // Marcar token como usado parcialmente
        $accessToken->update([
            'metadata' => ['basic_data_completed' => true, 'completed_at' => now()],
        ]);
        
        Log::info('Datos básicos guardados para profesor:', [
            'user_id' => $user->id,
            'teacher_id' => $teacher->id,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? 'null'
        ]);
        
        DB::commit();
        
        // Redirigir a success
        return redirect()->route('teacher.access.form', [
            'token' => $accessToken->token,
            'message' => 'basic_data_saved'
        ]);
    } */

    private function handleBasicDataForm(Request $request, User $user, TeacherAccessToken $accessToken)
    {
        // Validar datos básicos
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:200',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'consent_rgpd' => 'required|accepted',
        ]);
        
        // Actualizar usuario
        $user->update([
            'email' => $validated['email'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        ]);
        
        // Buscar o crear profesor
        $teacher = CampusTeacher::where('user_id', $user->id)->first();
        
        if ($teacher) {
            // Actualizar profesor existente
            $teacher->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);
        } else {
            // Crear nuevo profesor
            $teacher = CampusTeacher::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'teacher_code' => 'TCH' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
        }
        
        // ========== NUEVO: GENERAR PDF DE CONSENTIMIENTO ==========
        $season = CampusSeason::where('is_current', true)->first();
        $seasonSlug = $season->slug ?? date('Y');
        $acceptedAt = now();

        // Calcular checksum similar a Treasury
        $checksum = hash('sha256', implode('|', [
            $user->id,
            $seasonSlug,
            $acceptedAt->timestamp,
            $user->id, // En este caso, el profesor se auto-registra
        ]));

        // Generar PDF
        $pdf = Pdf::loadView('pdfs.teacher-consent', [
            'user' => $user,
            'teacher' => $teacher,
            'season' => $season,
            'acceptedAt' => $acceptedAt,
            'delegatedBy' => null, // No hay delegación en este flujo
            'delegatedReason' => null,
            'checksum' => $checksum,
        ]);
        
        // Ruta para guardar el PDF
        $path = "consents/teachers/{$user->id}/{$seasonSlug}.pdf";
        
        // Guardar el PDF
        Storage::disk('local')->put($path, $pdf->output());
        
        // Registrar en ConsentHistory
        ConsentHistory::updateOrCreate([
            'teacher_id' => $user->id,
            'season' => $seasonSlug,
        ], [
            'accepted_at' => $acceptedAt,
            'checksum' => $checksum,
            'document_path' => $path,
            'delegated_by_user_id' => null,
            'delegated_reason' => null,
        ]);
        
        // Opcional: Guardar también en TreasuryData como en Treasury
        TreasuryData::updateOrCreate(
            [
                'teacher_id' => $user->id,
                'key' => 'consent_signed_at',
            ],
            [
                'value' => $acceptedAt->toDateTimeString(),
            ]
        );
        // ========== FIN GENERACIÓN PDF ==========
        
        // Marcar token como usado parcialmente
        $accessToken->update([
            'metadata' => ['basic_data_completed' => true, 'completed_at' => now()],
        ]);
        
        Log::info('Datos básicos guardados para profesor:', [
            'user_id' => $user->id,
            'teacher_id' => $teacher->id,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? 'null'
        ]);
        
        DB::commit();
        
        // Redirigir a success
        return redirect()->route('teacher.access.form', [
            'token' => $accessToken->token,
            'message' => 'basic_data_saved'
        ]);
    }
    
    private function handlePaymentDataForm(Request $request, User $user, TeacherAccessToken $accessToken)
    {
          
        // Validar datos de pago con reglas condicionales
        // pròpia,  cedida,  exempta
        $validator = Validator::make($request->all(), [
            'payment_option' => 'required|in:own_fee,ceded_fee,waived_fee',
            'season_id' => 'required|string',
            'course_title' => 'required|string',
            'courseasignat-hours' => 'required|string',
            
            // Solo requeridos si no es waived_fee
            'fiscal_id' => 'required_if:payment_option,own_fee,ceded_fee|nullable|string|max:20',
            'address' => 'required_if:payment_option,own_fee,ceded_fee|nullable|string|max:255',
            'postal_code' => 'required_if:payment_option,own_fee,ceded_fee|nullable|string|max:10',
            'city' => 'required_if:payment_option,own_fee,ceded_fee|nullable|string|max:100',
            'iban' => 'required_if:payment_option,own_fee,ceded_fee|nullable|string|size:24',
            'bank_swift' => 'required_if:payment_option,own_fee,ceded_fee|nullable|string|max:255',
            
            
            // Declaración fiscal
            'declaracio_fiscal' => 'required|accepted',
            'autoritzacio_dades' => 'required|accepted',
        ]);
        
        // Validación personalizada para IBAN
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('iban')) {
                if (!$this->validateIBAN($request->input('iban'))) {
                    $validator->errors()->add('iban', 'El format de l\'IBAN no és vàlid. Ha de tenir 24 caràcters (2 lletres + 22 dígits).');
                }
            }
            
            // Validar que solo se seleccione una opción fiscal
            $fiscalSituations = $request->input('fiscal_situation', []);
            $otherFiscal = $request->input('fiscal_situation_other');
            
            if (empty($fiscalSituations) && empty($otherFiscal)) {
                $validator->errors()->add('fiscal_situation', 'Selecciona almenys una opció de situació fiscal.');
            }
            
            // Validar que no se seleccionen múltiples opciones (excepto si una es "altres")
            $selectedCount = count($fiscalSituations);
            if ($selectedCount > 1 && !in_array('altres', $fiscalSituations)) {
                $validator->errors()->add('fiscal_situation', 'Selecciona només una opció de situació fiscal.');
            }
        });
        
        if ($validator->fails()) {
            //   dd( "en handleBasicDataForm validator recibimos: ", $validator->fails());

            return back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $validated = $validator->validated();
        
        // Buscar profesor
        $teacher = CampusTeacher::where('user_id', $user->id)->firstOrFail();
        
        // Buscar curso y asignación
        $courseasignat = CampusCourseTeacher::where('teacher_id', $teacher->id)->first();
        $course = $courseasignat ? CampusCourse::find($courseasignat->course_id) : null;
        $season = CampusSeason::where('slug', $validated['season_id'])->first();
        
        // Determinar situación fiscal
        $fiscalSituation = '';
        $fiscalSituations = $request->input('fiscal_situation', []);
        $otherFiscal = $request->input('fiscal_situation_other', '');
        
        if (in_array('altres', $fiscalSituations) && !empty($otherFiscal)) {
            $fiscalSituation = $otherFiscal;
        } elseif (!empty($fiscalSituations)) {
            // Tomar la primera opción seleccionada
            $fiscalSituation = $fiscalSituations[0];
        }
        
        // Preparar metadata
        $metadata = [
            'course_title' => $validated['course_title'],
            'hours_assigned' => $validated['courseasignat-hours'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'fiscal_situation_selected' => $fiscalSituations,
            'fiscal_situation_other' => $otherFiscal,
            'completed_at' => now()->toDateTimeString(),
        ];
        
        // dd( "en handleBasicDataForm linea 321 Preparar metadata: ", $metadata);

        // Verificar si ya existe un registro de pago
        $existingPayment = CampusTeacherPayment::where('teacher_id', $teacher->id)
            ->where('season_id', $season->id ?? null)
            ->where('course_id', $course->id ?? null)
            ->first();
        
      
        $paymentOption = $validated['payment_option'];

        // Preparar datos para pago
        $paymentData = [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id ?? null,
            'season_id' => $season->id ?? null,
            'payment_option' => $paymentOption, // El mutator se encargará de la conversión
            'first_name' => $teacher->first_name,
            'last_name' => $teacher->last_name,
            'fiscal_id' => $validated['fiscal_id'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'city' => $validated['city'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'bank_holder' => $validated['bank_holder'] ?? null,
            'fiscal_situation' => $fiscalSituation,
            'metadata' => $metadata,
        ];

        //   dd( "en handleBasicDataForm linea 321 Preparar metadata: ", $paymentData);
        

        
        // Añadir campos adicionales
        if (isset($validated['address'])) {
            $paymentData['address'] = $validated['address'];
        }
        
        if (isset($validated['bank_swift'])) {
            $paymentData['bank_holder'] = $validated['bank_swift'];
        }
        
        //   dd( "en handleBasicDataForm linea 321 Preparar metadata: ", $paymentData);

        if ($existingPayment) {
            // Actualizar registro existente
            $existingPayment->update($paymentData);
            Log::info('Pago actualizado:', ['payment_id' => $existingPayment->id]);
        } else {
            // Crear nuevo registro
            CampusTeacherPayment::create($paymentData);
            Log::info('Nuevo pago creado');
        }

       
        
        // Actualizar profesor con datos fiscales si son propios
        if (in_array($validated['payment_option'], ['own_fee', 'ceded_fee'])) {
            $teacherMetadata = $teacher->metadata ?? [];
            //  dd( "en handleBasicDataForm  Preparar : ", $validated['fiscal_id']);
            // Actualizar metadata del profesor
            $teacher->update([
                'dni' => $validated['fiscal_id'] ?? null,
                'metadata' => array_merge($teacherMetadata, [
                    'fiscal_id' => $validated['fiscal_id'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'postal_code' => $validated['postal_code'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'iban' => $validated['iban'] ?? null,
                    'bank_holder' => $validated['bank_swift'] ?? null,
                ]),
            ]);
        }
        
        // Marcar token como completamente usado
        $accessToken->update([
            'used_at' => now(),
            'metadata' => array_merge(
                $accessToken->metadata ?? [],
                [
                    'payment_data_completed' => true, 
                    'payment_completed_at' => now()->toDateTimeString(),
                    'payment_option' => $validated['payment_option']
                ]
            ),
        ]);
        
        Log::info('Datos de pago guardados:', [
            'teacher_id' => $teacher->id,
            'payment_option' => $validated['payment_option'],
            'fiscal_id' => $validated['fiscal_id'] ?? 'null',
        ]);
        
        DB::commit();
        
        // Redirigir a success
        $messageType = $validated['payment_option'] === 'waived_fee' 
            ? 'waived_payment_saved' 
            : 'payment_saved';
        
        return redirect()->route('teacher.access.success', [
            'token' => $accessToken->token,
            'message' => $messageType
        ]);
    }
    
    /**
     * Validar formato IBAN
     */
    private function validateIBAN($iban)
    {
        // Limpiar espacios
        $iban = strtoupper(str_replace(' ', '', $iban));
        
        // Validar longitud (24 caracteres para España)
        if (strlen($iban) !== 24) {
            return false;
        }
        
        // Validar que empieza con ES
        if (substr($iban, 0, 2) !== 'ES') {
            return false;
        }
        
        // Validar que los siguientes 22 caracteres son dígitos
        $digits = substr($iban, 2);
        if (!ctype_digit($digits)) {
            return false;
        }
        
        return true;
    }
    
    // Nueva función para mostrar success
    public function success(Request $request, string $token)
    {
        // Buscar token
        $accessToken = TeacherAccessToken::where('token', $token)->firstOrFail();
        $user = User::findOrFail($accessToken->teacher_id);
        $teacher = CampusTeacher::where('user_id', $user->id)->first();
        
        // Obtener último consentimiento
        $latestConsent = null;
        if ($teacher) {
            $latestConsent = ConsentHistory::where('teacher_id', $teacher->id)
                ->latest('accepted_at')
                ->first();
        }
        
        // Determinar mensaje según parámetro
        $message = $request->input('message', 'default');
        $messages = [
            'basic_data_saved' => 'Les dades personals s\'han registrat correctament.',
            'payment_saved' => 'Les dades de pagament s\'han registrat correctament.',
            'waived_payment_saved' => 'Has renunciat voluntàriament al cobrament. Les dades s\'han registrat correctament.',
            'default' => 'Les dades s\'han registrat correctament.',
        ];
        
        return view('teacher-access.success', [
            'token' => $accessToken,
            'user' => $user,
            'teacher' => $teacher,
            'latestConsent' => $latestConsent,
            'message' => $messages[$message] ?? $messages['default']
        ]);
    }
}