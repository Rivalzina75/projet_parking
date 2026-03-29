<?php

use App\Models\WaitingListEntry;
use App\Services\ParkingService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    app(ParkingService::class)->closeExpiredReservations();
})->everyMinute()->name('parking-close-expired');

Schedule::call(function () {
    if (WaitingListEntry::exists()) {
        app(ParkingService::class)->assignSpotToNextWaitingUser();
    }
})->everyMinute()->name('parking-assign-waiting');
