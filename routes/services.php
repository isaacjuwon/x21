<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('services')->name('services.')->group(function () {
    Route::livewire('airtime', 'pages::services.airtime')->name('airtime');
    Route::livewire('data', 'pages::services.data')->name('data');
    Route::livewire('cable', 'pages::services.cable')->name('cable');
    Route::livewire('electricity', 'pages::services.electricity')->name('electricity');
    Route::livewire('education', 'pages::services.education')->name('education');
});
