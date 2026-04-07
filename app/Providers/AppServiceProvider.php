<?php

namespace App\Providers;

use App\Models\Param;
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
                if (! Schema::hasTable('params')) {
                    $doubleConsentEnabled = false;
                } else {
                    $doubleConsentEnabled = Param::getBoolValue(Param::DOUBLE_CONSENT_ENABLED, false);
                }
            } catch (\Throwable) {
                $doubleConsentEnabled = false;
            }

            $view->with('doubleConsentEnabled', $doubleConsentEnabled);
        });
    }
}
