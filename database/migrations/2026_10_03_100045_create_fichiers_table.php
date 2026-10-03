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
    Schema::create('fichiers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('dossier_id')->constrained()->cascadeOnDelete();
        $table->string('chemin');
        $table->string('nom_original');
        $table->string('mime');
        $table->unsignedBigInteger('taille');
        $table->string('categorie')->default('scan_initial'); // scan_initial | piece_traitement
        $table->foreignId('televerse_par')->constrained('users');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichiers');
    }
};
