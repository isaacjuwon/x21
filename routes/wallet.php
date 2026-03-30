<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('wallet', 'pages::wallet.index')->name('wallet.index');
    Route::livewire('wallet/transfer', 'pages::wallet.transfer')->name('wallet.transfer');
    Route::livewire('wallet/withdraw', 'pages::wallet.withdraw')->name('wallet.withdraw');
});
