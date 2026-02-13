<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CampusTeacher;
use App\Models\CampusTeacherPayment;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TreasuryController extends Controller
{
    use AuthorizesRequests;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:treasury');
    }

    /**
     * Display the treasury dashboard.
     */
    public function dashboard()
    {
        $this->authorize('campus.teachers.view');
        
        // Estadísticas básicas
        $pendingPayments = CampusTeacherPayment::whereNull('payment_option')->count();
        $approvedPayments = CampusTeacherPayment::whereNotNull('payment_option')->count();
        $activeTeachers = CampusTeacher::where('status', 'active')->count();
        
        // Calcular importe total (simulado - necesitaría lógica real)
        $totalAmount = CampusTeacherPayment::whereNotNull('payment_option')->count() * 100; // Ejemplo
        
        // Datos para el dashboard existente
        $data = [
            'teachers_total' => $activeTeachers,
            'teachers_pending_rgpd' => $pendingPayments, // Simulado - necesitaría lógica real de RGPD
            'teachers_with_rgpd' => $approvedPayments, // Simulado - necesitaría lógica real de RGPD
            'last_consents' => [], // Array vacío por ahora
        ];
        
        return view('dashboard.treasury', compact('data'));
    }

    /**
     * Display payments listing.
     */
    public function payments()
    {
        $payments = CampusTeacherPayment::with(['teacher', 'course', 'season'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('treasury.payments.index', compact('payments'));
    }

    /**
     * Display teachers listing for treasury.
     */
    public function teachers()
    {
        $teachers = CampusTeacher::with(['user', 'courses'])
            ->orderBy('last_name')
            ->paginate(20);
            
        return view('treasury.teachers.index', compact('teachers'));
    }

    /**
     * Display financial reports.
     */
    public function reports()
    {
        return view('treasury.reports.index');
    }
}
