<?php

use App\Enums\CandidatureStatut;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();

            $table->uuid('edit_token')->unique();
            $table->string('code_suivi')->nullable()->unique();

            $table->string('statut')->default(CandidatureStatut::BROUILLON->value);
            $table->unsignedTinyInteger('etape_courante')->default(1);

            $table->string('derniere_formation')->nullable();
            $table->string('etablissement_provenance')->nullable();
            $table->text('motivation')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_identity_at')->nullable();

            $table->timestamps();

            $table->unique(['candidat_id', 'programme_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
