<?php

use App\Models\Param;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('params', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('value');
            $table->timestamps();
        });

        // Ajouter un paramètre ne nécessite plus de nouvelle colonne SQL.
        Param::setValue(Param::DEFAULT_RESERVATION_HOURS, 8);
        Param::setValue(Param::DOUBLE_CONSENT_ENABLED, false);
    }

    public function down(): void
    {
        Schema::dropIfExists('params');
    }
};
