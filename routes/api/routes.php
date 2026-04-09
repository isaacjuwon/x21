<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;



Route::prefix('v1')->name('api.')->group(function () {
    require base_path('routes/api/v1.php');
});