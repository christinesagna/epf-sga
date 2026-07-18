<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('slug')->unique();
            $table->string('niveau'); // licence, master, classe_preparatoire
            $table->unsignedInteger('capacite_accueil')->default(0);
            $table->date('date_ouverture')->nullable();
            $table->date('date_fermeture')->nullable();
            $table->decimal('frais_scolarite', 12, 2)->nullable();
            $table->text('echeancier_paiement')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
