<?php

use Illuminate\Support\Facades\Route;
use MatJeninStudio\ContactApprovable\Http\Controllers\ContactController;

Route::prefix('contact-approvable')
    ->name('contact-approvable.')
    ->group(function () {
        // Dashboard
        Route::get('/', function () {
            return view('contact-approvable::dashboard');
        })->name('dashboard');

        // Contacts
        Route::resource('contacts', ContactController::class);

        // Approvals
        Route::get('/approvals', function () {
            return view('contact-approvable::approvals.index');
        })->name('approvals.index');

        // Approval Records
        Route::get('/approval-records', function () {
            return view('contact-approvable::approval-records.index');
        })->name('approval-records.index');

        // Settings
        Route::get('/settings', function () {
            return view('contact-approvable::settings');
        })->name('settings');
    });
