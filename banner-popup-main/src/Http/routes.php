<?php

use App\Http\Controllers\BannerPopupController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('banner-popup.route_prefix', 'banner-popup'))
    ->middleware(config('banner-popup.route_middleware', ['web']))
    ->name('banner-popup.')
    ->group(function () {

        Route::get('/active', [BannerPopupController::class, 'active'])
            ->name('active');

        Route::post('/{banner}/seen', [BannerPopupController::class, 'seen'])
            ->name('seen');

    });
