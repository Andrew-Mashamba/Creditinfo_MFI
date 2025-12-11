<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Mfi\AllInstitutions;
use App\Livewire\Mfi\CreateNewMfi;
use App\Livewire\Mfi\InstanceConfiguration;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // MFI Management Routes
    Route::get('/mfi/all-institutions', AllInstitutions::class)->name('mfi.all-institutions');
    Route::get('/mfi/create-new', CreateNewMfi::class)->name('mfi.create-new');
    Route::get('/mfi/instance-configuration', InstanceConfiguration::class)->name('mfi.instance-configuration');
});
