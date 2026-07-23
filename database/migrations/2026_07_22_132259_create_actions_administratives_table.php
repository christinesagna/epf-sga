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
        Schema::create('actions_administratives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auteur_id')->constrained('users')->restrictOnDelete();
            $table->string('action')->index();
            $table->foreignId('utilisateur_cible_id')->constrained('users')->restrictOnDelete();
            $table->json('anciennes_valeurs')->nullable();
            $table->json('nouvelles_valeurs')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions_administratives');
    }
};
