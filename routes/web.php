<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/wallet.php';
require __DIR__.'/loan.php';
require __DIR__.'/shares.php';
require __DIR__.'/services.php';
require __DIR__.'/tickets.php';
require __DIR__.'/kyc.php';
require __DIR__.'/support.php';
