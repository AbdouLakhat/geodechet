<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crée les informations principales d’un signalement et son lien vers une unité.
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unite_id')->nullable()->constrained('unites');
            $table->string('type_dechet');
            $table->string('description')->nullable();
            $table->string('photo')->nullable();
            $table->string('statut')->default('en attente');
            $table->date('date_signalement');
            $table->timestamps();
        });

        // Stocke l’emplacement du dépôt sous forme de point géographique.
        DB::statement('ALTER TABLE signalements ADD COLUMN position geometry(Point, 4326)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signalements');
    }
};
