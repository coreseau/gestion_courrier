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
    Schema::create('dossiers', function (Blueprint $table) {
        $table->id();
        $table->string('reference')->unique();
        $table->string('objet');
        $table->text('resume')->nullable();
        $table->string('origine_type');            // direction | entreprise
        $table->string('origine_nom');
        $table->date('date_reception');
        $table->string('priorite')->default('normale'); // normale | urgente | tres_urgente
        $table->string('statut')->default('recu');
        $table->date('echeance')->nullable();
        $table->foreignId('enregistre_par')->constrained('users');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
