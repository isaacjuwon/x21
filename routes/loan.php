<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('loans', 'pages::loan.index')->name('loan.index');
    Route::livewire('loans/apply', 'pages::loan.apply')->name('loan.apply');
    Route::livewire('loans/{loan}', 'pages::loan.view')->name('loan.view');
});
