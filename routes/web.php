<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::get('/', function () { return view('admin.dashboard'); });
    Route::get('/dashboard', function () { return view('admin.dashboard'); });
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class)->names('admin.employees');
    Route::resource('work-places', \App\Http\Controllers\Admin\WorkPlaceController::class)->names('admin.work_places');
    Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class)->names('admin.shifts');
    Route::resource('shift-time-rules', \App\Http\Controllers\Admin\ShiftTimeRuleController::class)->names('admin.shift_time_rules');
    // Import routes placed BEFORE resource to avoid matching {schedule} with "import"
    Route::get('schedules/import', [\App\Http\Controllers\Admin\ScheduleImportController::class, 'create'])->name('admin.schedules.import');
    Route::post('schedules/import', [\App\Http\Controllers\Admin\ScheduleImportController::class, 'store'])->name('admin.schedules.import.store');
    // Constrain {schedule} param to numeric only
    Route::resource('schedules', \App\Http\Controllers\Admin\ScheduleController::class)
        ->names('admin.schedules')
        ->whereNumber('schedule');
    Route::post('jobs/generate', [\App\Http\Controllers\Admin\AttendanceJobController::class, 'generate'])->name('admin.attendance_jobs.generate');
    Route::post('jobs/run', [\App\Http\Controllers\Admin\AttendanceJobController::class, 'run'])->name('admin.attendance_jobs.run');
    Route::post('jobs/randomize-seconds', [\App\Http\Controllers\Admin\AttendanceJobController::class, 'randomizeSeconds'])->name('admin.attendance_jobs.randomize_seconds');
    Route::resource('jobs', \App\Http\Controllers\Admin\AttendanceJobController::class)
        ->names('admin.attendance_jobs')
        ->parameters(['jobs' => 'attendance_job'])
        ->whereNumber('attendance_job');
    Route::get('logs/whatsapp', [\App\Http\Controllers\Admin\AttendanceJobController::class, 'logs'])->name('admin.logs.whatsapp');
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('admin.settings.edit');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('settings/test', [\App\Http\Controllers\Admin\SettingController::class, 'test'])->name('admin.settings.test');
    Route::get('sessions', [\App\Http\Controllers\Admin\SessionController::class, 'index'])->name('admin.sessions.index');
    Route::post('sessions/start', [\App\Http\Controllers\Admin\SessionController::class, 'start'])->name('admin.sessions.start');
    Route::post('sessions/logout', [\App\Http\Controllers\Admin\SessionController::class, 'logout'])->name('admin.sessions.logout');
});

Route::post('/webhook/session', [\App\Http\Controllers\WebhookController::class, 'session'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhook/message', [\App\Http\Controllers\WebhookController::class, 'message'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
