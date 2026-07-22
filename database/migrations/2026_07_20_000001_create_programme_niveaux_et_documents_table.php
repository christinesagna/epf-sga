<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveaux', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->timestamps();
        });

        Schema::create('programme_niveaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('niveau_id')
                ->constrained('niveaux')
                ->restrictOnDelete();
            $table->unsignedSmallInteger('ordre')->default(1);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['programme_id', 'niveau_id'], 'programme_niveau_unique');
        });

        Schema::create('programme_niveau_type_document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_niveau_id')
                ->constrained('programme_niveaux')
                ->cascadeOnDelete();
            $table->foreignId('type_document_id')
                ->constrained('types_documents')
                ->cascadeOnDelete();
            $table->boolean('obligatoire')->default(true);
            $table->unsignedSmallInteger('ordre')->default(1);
            $table->timestamps();

            $table->unique(
                ['programme_niveau_id', 'type_document_id'],
                'programme_niveau_document_unique',
            );
        });

        Schema::table('candidatures', function (Blueprint $table) {
            $table->foreignId('programme_niveau_id')
                ->nullable()
                ->after('programme_id')
                ->constrained('programme_niveaux')
                ->nullOnDelete();
        });

        Schema::dropIfExists('programme_type_document');
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('programme_niveau_id');
        });

        Schema::dropIfExists('programme_niveau_type_document');
        Schema::dropIfExists('programme_niveaux');
        Schema::dropIfExists('niveaux');

        Schema::create('programme_type_document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_document_id')->constrained('types_documents')->cascadeOnDelete();
            $table->boolean('obligatoire')->default(true);
            $table->unsignedSmallInteger('ordre')->default(1);
            $table->timestamps();

            $table->unique(['programme_id', 'type_document_id']);
        });
    }
};
