<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('programme_type_document');
    }
};
