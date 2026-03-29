<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter lastname en second après name
            if (! Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname')->default('')->after('name');
            }

            // Ajouter role après password
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('password');
            }

            // Ajouter is_validated après role
            if (! Schema::hasColumn('users', 'is_validated')) {
                $table->boolean('is_validated')->default(false)->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('lastname', 'role', 'is_validated');
        });
    }
};
