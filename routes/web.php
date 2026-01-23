<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PushLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventTypeController;
use App\Http\Controllers\Admin\EventQuestionController;
use App\Http\Controllers\Admin\EventAnswerController;
use App\Http\Controllers\Admin\EventQuestionTemplateController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Campus\CategoryController;

use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;



// Language
Route::post('/set-locale', [LocaleController::class, 'set'])->name('set-locale');
Route::post('/language/resolve-conflict', [LocaleController::class, 'resolveConflict'])
    ->name('language.resolve-conflict');

    
// Rutas públicas
Route::get('/', fn () => view('welcome'));

// Auth
require __DIR__.'/auth.php';

//  Rutas protegidas por login y verificación
Route::middleware(['auth', 'verified'])->group(function () {

    //  Dashboard principal
  //  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    //  Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //  Rutas Admin (roles, permisos, usuarios)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('can:users.index')->resource('users', AdminUserController::class);
        Route::middleware('can:roles.index')->resource('roles', RoleController::class);
        Route::middleware('can:permissions.index')->resource('permissions', PermissionController::class);
    });

    //  Configuración del sistema (logo, idioma)
    Route::middleware('can:admin.access')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('/settings/logo', [SettingsController::class, 'updateLogo'])->name('settings.updateLogo');
        Route::put('/settings/language', [SettingsController::class, 'updateLanguage'])->name('settings.updateLanguage');
    });

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::put('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
        // Otras rutas relacionadas...
    });

    Route::middleware('auth')->group(function () {
    // Nueva ruta para actualizar idioma de usuario
        Route::put('/profile/language', [ProfileController::class, 'updateLanguage'])
            ->name('profile.language.update');
});
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
       Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('admin.feedback.index');
       Route::delete('/feedback/{id}', [AdminFeedbackController::class, 'destroy'])->name('admin.feedback.destroy');
    });

    //  Logs Push relacionados con notificaciones
    Route::prefix('settings/push-logs')->name('push.logs.')->middleware('can:notifications.logs')->group(function () {
        Route::get('/', [PushLogController::class, 'index'])->name('');
        Route::get('/download/{filename}', [PushLogController::class, 'download'])->name('download');
        Route::delete('/delete/{filename}', [PushLogController::class, 'delete'])->name('delete');
    });

    //  Notificaciones (CRUD completo + acciones)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index')->middleware('permission:notifications.view');
        Route::get('/create', [NotificationController::class, 'create'])->name('create')->middleware('permission:notifications.create');
        Route::post('/', [NotificationController::class, 'store'])->name('store')->middleware('permission:notifications.create');

        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show')->middleware('permission:notifications.view');
        Route::get('/{notification}/edit', [NotificationController::class, 'edit'])->name('edit')->middleware('permission:notifications.edit');
        Route::put('/{notification}', [NotificationController::class, 'update'])->name('update')->middleware('permission:notifications.edit');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy')->middleware('permission:notifications.delete');

        Route::post('/{notification}/publish', [NotificationController::class, 'publish'])->name('publish')->middleware('permission:notifications.publish');
        Route::post('/{notification}/send-push', [NotificationController::class, 'sendPush'])->name('send-push')->middleware('permission:notifications.publish');

        Route::post('/mark-as-read/{notification}', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    
        // Estructura para las rutas de envío:
        Route::post('/{notification}/send-email', [NotificationController::class, 'sendEmail'])
            ->name('send-email')
            ->middleware('permission:notifications.publish');
        
        Route::post('/{notification}/send-web', [NotificationController::class, 'sendWeb'])
            ->name('send-web')
            ->middleware('permission:notifications.publish');
        
        Route::post('/{notification}/send-push', [NotificationController::class, 'sendPush'])
            ->name('send-push')
            ->middleware('permission:notifications.publish');
    });


    // API interna para frontend (no REST)
    Route::prefix('api')->group(function () {
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
        Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    });

    // Rutas administrativas
    Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
        // Rutas para respuestas de eventos
        Route::get('events/{event}/answers/export/{format}', [EventAnswerController::class, 'export'])
            ->name('events.answers.export'); // Nombre: admin.events.answers.export
            
        Route::get('events/{event}/answers/print', [EventAnswerController::class, 'print'])
            ->name('events.answers.print'); // Nombre: admin.events.answers.print    

        // Exportar eventos
        /* Route::get('/events/{event}/export-pdf', [EventController::class, 'exportAnswersToPDF'])
            ->name('events.export.pdf');
        Route::get('/events/{event}/export-excel', [EventController::class, 'exportAnswersToExcel'])
            ->name('events.export.excel'); */

        // Event Types Routes
        Route::resource('event-types', EventTypeController::class)->except(['show']);
        
        // Events Routes
        Route::resource('events', EventController::class);

        // Rutas para preguntas de eventos
        Route::resource('events.questions', EventQuestionController::class)->except(['show']);
        
        // Rutas para respuestas de eventos
        Route::resource('events.answers', EventAnswerController::class);

        // Rutas para plantillas de preguntas 
        Route::resource('event-question-templates', EventQuestionTemplateController::class)->except(['show']);

        Route::get('question-templates/{templateId}/questions', [EventQuestionTemplateController::class, 'getQuestions'])->name('question-templates.questions');

        // API para plantillas
        Route::get('event-question-templates/api/list', [EventQuestionTemplateController::class, 'apiIndex'])
            ->name('event-question-templates.api');
        
          
    });

    // Rutas públicas del calendario
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/event/{event}', [CalendarController::class, 'show'])->name('calendar.event.show');
    Route::get('/calendar/event/{event}/details', [CalendarController::class, 'eventDetails'])->name('calendar.event.details');
    Route::post('/calendar/event/answers', [CalendarController::class, 'saveAnswers'])->name('calendar.event.answers');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

    // Rutes del Campus
    Route::prefix('campus')->name('campus.')->middleware(['auth'])->group(function () {
        // Perfil personal
        Route::get('/profile', function () {
            return view('campus.profile');
        })->name('profile')->middleware('can:campus.profile.view');
        
        // Cursos (estudiants)
        Route::get('/my-courses', function () {
            return view('campus.my-courses');
        })->name('my-courses')->middleware('can:campus.my_courses.view');
        
        // Matriculacions (estudiants)
        Route::get('/my-registrations', function () {
            return view('campus.my-registrations');
        })->name('my-registrations')->middleware('can:campus.my_courses.view');
        
        // Cursos (professors)
        Route::get('/teacher-courses', function () {
            return view('campus.teacher-courses');
        })->name('teacher-courses')->middleware('can:campus.my_courses.manage');
        
        // Estudiants (professors)
        Route::get('/teacher-students', function () {
            return view('campus.teacher-students');
        })->name('teacher-students')->middleware('can:campus.students.view');
        
        // Catàleg de cursos
        Route::get('/catalog', function () {
            return view('campus.catalog');
        })->name('catalog')->middleware('can:campus.courses.view');
        
        // Matricular-se
        Route::get('/enroll', function () {
            return view('campus.enroll');
        })->name('enroll')->middleware('can:campus.courses.enroll');
        
        // CRUD del campus (admin/gestor)
        Route::resource('categories', CategoryController::class)
            ->middleware('can:campus.categories.view'); 
        // Rutas para Categories
        /* Route::resource('categories', CategoryController::class)
            ->names([
                'index' => 'campus.categories.index',
                'create' => 'campus.categories.create',
                'store' => 'campus.categories.store',
                'show' => 'campus.categories.show',
                'edit' => 'campus.categories.edit',
                'update' => 'campus.categories.update',
                'destroy' => 'campus.categories.destroy',
            ])
            ->middleware('can:campus.categories.view'); */

        // Rutas adicionales para Categories

        Route::post('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])
            ->name('categories.toggleActive')
            ->middleware('can:campus.categories.edit');

        Route::post('categories/{category}/toggle-featured', [CategoryController::class, 'toggleFeatured'])
            ->name('categories.toggleFeatured')
            ->middleware('can:campus.categories.edit');

        // seasons
        Route::resource('seasons', \App\Http\Controllers\Campus\SeasonController::class)
            ->middleware('can:campus.seasons.view');

        Route::post('seasons/{season}/set-as-current', [\App\Http\Controllers\Campus\SeasonController::class, 'setAsCurrent'])
        ->name('seasons.setAsCurrent')
        ->middleware('can:campus.seasons.edit');

        Route::post('seasons/{season}/toggle-active', [\App\Http\Controllers\Campus\SeasonController::class, 'toggleActive'])
            ->name('seasons.toggleActive')
            ->middleware('can:campus.seasons.edit');
        
        Route::resource('courses', \App\Http\Controllers\Campus\CourseController::class)
            ->middleware('can:campus.courses.view');
        
        Route::resource('students', \App\Http\Controllers\Campus\StudentController::class)
            ->middleware('can:campus.students.view');
        
        Route::resource('teachers', \App\Http\Controllers\Campus\TeacherController::class)
            ->middleware('can:campus.teachers.view');
        
        Route::resource('registrations', \App\Http\Controllers\Campus\RegistrationController::class)
            ->middleware('can:campus.registrations.view'); 
            
    });
    

});
