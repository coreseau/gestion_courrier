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
    Schema::create('traitements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('dossier_id')->constrained()->cascadeOnDelete();
        $table->foreignId('collaborateur_id')->constrained('users');
        $table->string('reference_traitement')->nullable();
        $table->longText('contenu')->nullable();
        $table->string('statut')->default('brouillon'); // brouillon | soumis
        $table->timestamp('soumis_le')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traitements');
    }
};
