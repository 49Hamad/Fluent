<?php

use App\Livewire\FormFeedBack;
use App\Http\Controllers\FluentFrontendController;
use App\Http\Controllers\StudentApplicationController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Middleware\EnsureStudentIsAuthenticated;
use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage\ShowHomePage;

Route::get('/',ShowHomePage::class)->name('home');
Route::get('/feedback-form/{id}',FormFeedBack::class)->name('feedback_form');

/*
|--------------------------------------------------------------------------
| Phase 2 — real student application
|--------------------------------------------------------------------------
| /apply shows the approved form for the cohort that is open in Filament
| (or the approved closed / waitlist state). Submissions are stored in
| student_applications and appear in Filament → طلبات الطلاب.
*/
Route::get('/apply', [FluentFrontendController::class, 'apply'])->name('fluent.apply');
Route::post('/apply', [StudentApplicationController::class, 'store'])
    ->middleware('throttle:6,1')   // max 6 submissions per minute per visitor
    ->name('fluent.apply.store');

/*
|--------------------------------------------------------------------------
| Phase 2 — student sign-in + «مساحتي في Fluent»
|--------------------------------------------------------------------------
| Password-less: e-mail → 6-digit code (10 min, one use, rate-limited).
| Separate "student" guard — nothing to do with employees / Filament.
*/
Route::get('/login', [StudentAuthController::class, 'show'])->name('fluent.login');
Route::post('/login/code', [StudentAuthController::class, 'requestCode'])
    ->middleware('throttle:20,1')->name('fluent.login.code');
Route::post('/login/verify', [StudentAuthController::class, 'verify'])
    ->middleware('throttle:30,1')->name('fluent.login.verify');

Route::middleware(EnsureStudentIsAuthenticated::class)->group(function () {
    Route::get('/portal', [StudentPortalController::class, 'show'])->name('fluent.portal');
    Route::get('/portal/data', [StudentPortalController::class, 'data'])->name('fluent.portal.data');
    Route::post('/portal/logout', [StudentAuthController::class, 'logout'])->name('fluent.logout');
});

/*
|--------------------------------------------------------------------------
| Redesign — new public screens (FRONTEND REVIEW MODE)
|--------------------------------------------------------------------------
| Approved UI shown before its backend exists; nothing is sent or stored,
| nobody is signed in. All answer 404 unless config('fluent.frontend_preview')
| is true (default: every environment except production).
| See App\Http\Controllers\FluentFrontendController.
*/
Route::get('/challenge', [FluentFrontendController::class, 'challenge'])->name('fluent.challenge');

