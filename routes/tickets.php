<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('tickets')->name('tickets.')->group(function () {
    Route::livewire('/', 'pages::tickets.index')->name('index');
    Route::livewire('/create', 'pages::tickets.create')->name('create');
    Route::livewire('/{ticket}', 'pages::tickets.view')->name('show');
});
