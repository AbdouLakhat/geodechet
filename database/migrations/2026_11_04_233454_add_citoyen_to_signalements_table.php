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
        // Ajoute les coordonnées du citoyen et la date à laquelle il a donné son consentement.
        Schema::table('signalements', function (Blueprint $table) {
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email')->nullable();
            $table->timestamp('consentement_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->dropColumn(['nom', 'prenom', 'telephone', 'email', 'consentement_le']);
        });
    }
};
