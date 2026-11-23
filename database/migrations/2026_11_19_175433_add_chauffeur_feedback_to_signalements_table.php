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
        // Enregistre la preuve, la remarque du chauffeur et la date de traitement.
        Schema::table('signalements', function (Blueprint $table) {
            $table->string('photo_traitement')->nullable();
            $table->text('note_chauffeur')->nullable();
            $table->timestamp('date_traitement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->dropColumn(['photo_traitement', 'note_chauffeur', 'date_traitement']);
        });
    }
};
