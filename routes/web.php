<?php

use App\Livewire\FormFeedBack;
use App\Http\Controllers\FluentFrontendController;
use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage\ShowHomePage;

Route::get('/',ShowHomePage::class)->name('home');
Route::get('/feedback-form/{id}',FormFeedBack::class)->name('feedback_form');

/*
|--------------------------------------------------------------------------
| Redesign — new public screens (FRONTEND REVIEW MODE)
|--------------------------------------------------------------------------
| Approved UI shown before its backend exists; nothing is sent or stored,
| nobody is signed in. All answer 404 unless config('fluent.frontend_preview')
| is true (default: every environment except production).
| See App\Http\Controllers\FluentFrontendController.
*/
Route::get('/apply', [FluentFrontendController::class, 'apply'])->name('fluent.apply');
Route::get('/challenge', [FluentFrontendController::class, 'challenge'])->name('fluent.challenge');
Route::get('/login', [FluentFrontendController::class, 'login'])->name('fluent.login');
Route::get('/portal', [FluentFrontendController::class, 'portal'])->name('fluent.portal');

// DEVELOPMENT ONLY — student portal preview with in-memory mock data.
Route::get('/preview/portal', [FluentFrontendController::class, 'portalPreview'])->name('fluent.preview.portal');
