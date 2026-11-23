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
        // Ajoute le numéro de téléphone et le rôle utilisé pour contrôler les espaces accessibles.
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone')->nullable();
            $table->string('role')->default('chauffeur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'role']);
        });
    }
};
