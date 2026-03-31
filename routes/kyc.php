<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('kyc')->name('kyc.')->group(function () {
    Route::livewire('/', 'pages::kyc.index')->name('index');
});
