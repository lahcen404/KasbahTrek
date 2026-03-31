<?php

namespace App\Providers;

use App\Events\BookingStatusUpdated;
use App\Events\TripReportStatusUpdated;
use App\Events\VerificationStatusUpdated;
use App\Interfaces\AdminUserRepositoryInterface;
use App\Interfaces\AuthRepositoryInterface;
use App\Interfaces\BookingRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\TourRepositoryInterface;
use App\Interfaces\TripReportRepositoryInterface;
use App\Interfaces\VerificationRepositoryInterface;
use App\Listeners\SendBookingStatusNotification;
use App\Listeners\SendTripReportStatusNotification;
use App\Listeners\SendVerificationStatusNotification;
use App\Repositories\AdminUserRepository;
use App\Repositories\AuthRepository;
use App\Repositories\BookingRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\TourRepository;
use App\Repositories\TripReportRepository;
use App\Repositories\VerificationRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

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

    $this->app->bind(
        AdminUserRepositoryInterface::class,
        AdminUserRepository::class
    );

    $this->app->bind(
        VerificationRepositoryInterface::class,
        VerificationRepository::class
    );

    $this->app->bind(
        TripReportRepositoryInterface::class,
        TripReportRepository::class
    );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            BookingStatusUpdated::class,
            SendBookingStatusNotification::class
        );

        \Illuminate\Support\Facades\Event::listen(
            VerificationStatusUpdated::class,
            SendVerificationStatusNotification::class
        );

        \Illuminate\Support\Facades\Event::listen(
            TripReportStatusUpdated::class,
            SendTripReportStatusNotification::class
        );
    }
}
