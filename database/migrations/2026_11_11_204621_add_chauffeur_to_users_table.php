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
        // Ajoute les informations professionnelles et l’état du compte du chauffeur.
        Schema::table('users', function (Blueprint $table) {
            $table->string('numero_permis', 30)->nullable();
            $table->string('immatriculation', 20)->nullable();
            $table->boolean('disponible')->default(true);
            $table->boolean('actif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['numero_permis', 'immatriculation', 'disponible', 'actif']);
        });
    }
};
