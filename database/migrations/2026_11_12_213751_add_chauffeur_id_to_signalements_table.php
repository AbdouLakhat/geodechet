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
        // Relie chaque signalement à son chauffeur et conserve la mission si le compte est supprimé.
        Schema::table('signalements', function (Blueprint $table) {
            $table->foreignId('chauffeur_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chauffeur_id');
        });
    }
};
