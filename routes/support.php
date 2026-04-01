<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('support')->name('support.')->group(function () {
    Route::livewire('/chat', 'pages::support.chat')->name('chat');
});
