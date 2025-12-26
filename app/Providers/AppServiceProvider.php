<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Apartment;
use App\Policies\ApartmentPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    protected $policies = [
    Apartment::class => ApartmentPolicy::class,
    ];  
}
