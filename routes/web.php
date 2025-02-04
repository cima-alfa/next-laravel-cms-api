<?php

declare(strict_types=1);

use App\Http\Middleware\FrontRoutesMiddleware;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::name('front.')
    ->middleware(FrontRoutesMiddleware::class)
    ->group(function (): void {

        Route::name('cp.')
            ->prefix('/control-panel')
            ->middleware('front:auth')
            ->group(function (): void {

                Route::get('/dashboard')->name('dashboard.index');

                Route::name('pages.')
                    ->prefix('/pages')
                    ->group(function (): void {

                        Route::get('/')->name('index');
                        Route::get('/create')->name('create');
                        Route::get('/{pageId}')->whereUuid('pageId')->name('edit');

                    });

                Route::name('users.')
                    ->prefix('/users')
                    ->group(function (): void {

                        Route::get('/')->name('index');
                        Route::get('/create')->name('create');
                        Route::get('/{userId}')->whereUuid('userId')->name('profile');

                    });

                Route::get('/settings')->middleware('front:owner')->name('settings.index');

            });

        Route::middleware('front:auth')->group(function (): void {

            if (Features::enabled(Features::emailVerification())) {
                Route::get('/email/verify')->name('verification.notice');
                Route::get('/email/verify/{id}/{hash}')->name('verification.verify');
            }

        });

        Route::middleware('front:guest')->group(function (): void {

            Route::get('/login')->name('login');
            Route::get('/register')->name('register');

        });

    });

Route::middleware(FrontRoutesMiddleware::class)->group(function (): void {

    Route::get('/{permalink}')
        ->where(['permalink' => '.*', 'include-pattern:permalink' => true])
        ->middleware('front:permalink:frontpage')
        ->name('front.page.permalink');

});

Route::fallback(fn () => abort(400));
