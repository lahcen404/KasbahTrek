<?php

namespace App\Providers;

use App\Interfaces\AuthRepositoryInterface;
use App\Interfaces\BookingRepositoryInterface;
use App\Interfaces\TourRepositoryInterface;
use App\Repositories\AuthRepository;
use App\Repositories\BookingRepository;
use App\Repositories\TourRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
        TourRepositoryInterface::class,
        TourRepository::class
    );

    $this->app->bind(
        AuthRepositoryInterface::class,
        AuthRepository::class
    );

    $this->app->bind(
        BookingRepositoryInterface::class,
        BookingRepository::class
    );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
