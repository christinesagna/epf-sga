<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidature_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidature_id')->constrained()->cascadeOnDelete();

            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut');
            $table->string('acteur_type')->default('candidat'); // candidat, systeme, admin, jury
            $table->unsignedBigInteger('acteur_id')->nullable();

            $table->text('commentaire')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidature_historiques');
    }
};
