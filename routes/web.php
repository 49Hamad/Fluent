<?php

use App\Livewire\FormFeedBack;
use App\Http\Controllers\FluentFrontendController;
use App\Http\Controllers\StudentApplicationController;
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
| Redesign — new public screens (FRONTEND REVIEW MODE)
|--------------------------------------------------------------------------
| Approved UI shown before its backend exists; nothing is sent or stored,
| nobody is signed in. All answer 404 unless config('fluent.frontend_preview')
| is true (default: every environment except production).
| See App\Http\Controllers\FluentFrontendController.
*/
Route::get('/challenge', [FluentFrontendController::class, 'challenge'])->name('fluent.challenge');
Route::get('/login', [FluentFrontendController::class, 'login'])->name('fluent.login');
Route::get('/portal', [FluentFrontendController::class, 'portal'])->name('fluent.portal');

// DEVELOPMENT ONLY — student portal preview with in-memory mock data.
Route::get('/preview/portal', [FluentFrontendController::class, 'portalPreview'])->name('fluent.preview.portal');
