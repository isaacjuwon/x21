<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('wallet', 'pages::wallet.index')->name('wallet.index');
    Route::livewire('wallet/transfer', 'pages::wallet.transfer')->name('wallet.transfer');
    Route::livewire('wallet/withdraw', 'pages::wallet.withdraw')->name('wallet.withdraw');
    Route::livewire('wallet/fund', 'pages::wallet.fund')->name('wallet.fund');
    Route::livewire('wallet/transactions', 'pages::wallet.transactions')->name('wallet.transactions');
    Route::get('wallet/verify/{reference}', function (string $reference) {
        return redirect()->route('wallet.fund', ['reference' => $reference]);
    })->name('wallet.verify');
});
