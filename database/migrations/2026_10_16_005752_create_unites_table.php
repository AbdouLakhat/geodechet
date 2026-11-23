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
        // Crée la table des unités avant d’ajouter leurs contours géographiques PostGIS.
        Schema::create('unites', function (Blueprint $table) {
            $table->id();
            $table->integer('numero');
            $table->string('nom');
            $table->timestamps();
        });

        // Le système 4326 permet de stocker les coordonnées GPS des limites.
        DB::statement('ALTER TABLE unites ADD COLUMN contour geometry(Geometry, 4326)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unites');
    }
};
