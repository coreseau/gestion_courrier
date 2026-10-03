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
    Schema::create('cotations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('dossier_id')->constrained()->cascadeOnDelete();
        $table->foreignId('cotee_par')->constrained('users');
        $table->string('type')->default('initiale'); // initiale | transfert
        $table->text('instruction')->nullable();
        $table->text('motif_transfert')->nullable();
        $table->date('echeance')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotations');
    }
};
