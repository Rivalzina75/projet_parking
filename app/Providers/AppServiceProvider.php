<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('*', function ($view) {
            static $doubleConsentEnabled;

            if ($doubleConsentEnabled !== null) {
                $view->with('doubleConsentEnabled', $doubleConsentEnabled);

                return;
            }

            try {
                if (! Schema::hasTable('app_settings')) {
                    $doubleConsentEnabled = false;
                } else {
                    $doubleConsentEnabled = (bool) (DB::table('app_settings')->value('double_consent_enabled') ?? false);
                }
            } catch (\Throwable) {
                $doubleConsentEnabled = false;
            }

            $view->with('doubleConsentEnabled', $doubleConsentEnabled);
        });
    }
}
