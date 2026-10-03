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
    Schema::create('cotation_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cotation_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('role')->default('associe');     // pilote | associe
        $table->string('statut')->default('a_traiter'); // a_traiter | en_cours | soumis | transfere
        $table->timestamp('lu_le')->nullable();
        $table->timestamps();

        $table->unique(['cotation_id', 'user_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotation_user');
    }
};
