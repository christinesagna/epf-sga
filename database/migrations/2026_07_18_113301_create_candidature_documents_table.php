<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidature_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidature_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_document_id')->constrained('types_documents')->cascadeOnDelete();

            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->string('statut_validation')->default('en_attente');
            $table->text('commentaire_validation')->nullable();

            $table->timestamps();

            $table->unique(['candidature_id', 'type_document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidature_documents');
    }
};
