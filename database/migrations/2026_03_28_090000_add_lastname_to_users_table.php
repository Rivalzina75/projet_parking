<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cette migration est maintenant gérée par 2026_03_27_105708
        // On garde celle-ci au cas où, mais elle ne fait rien si les colonnes existent
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname')->default('')->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lastname')) {
                $table->dropColumn('lastname');
            }
        });
    }
};
