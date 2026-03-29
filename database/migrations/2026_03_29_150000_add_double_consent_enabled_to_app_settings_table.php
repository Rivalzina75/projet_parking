<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'double_consent_enabled')) {
                $table->boolean('double_consent_enabled')->default(false)->after('default_reservation_hours');
            }
        });

        DB::table('app_settings')
            ->whereNull('double_consent_enabled')
            ->update(['double_consent_enabled' => false]);
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (Schema::hasColumn('app_settings', 'double_consent_enabled')) {
                $table->dropColumn('double_consent_enabled');
            }
        });
    }
};
