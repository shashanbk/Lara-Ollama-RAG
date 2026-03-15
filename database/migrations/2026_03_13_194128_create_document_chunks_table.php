<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
{
    DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

    Schema::create('document_chunks', function (Blueprint $table) {
        $table->id();
        // This line requires the documents table to exist first!
        $table->foreignId('document_id')->constrained()->onDelete('cascade'); 
        $table->text('content');
        $table->jsonb('metadata')->nullable();
        $table->timestamps();
    });

    DB::statement('ALTER TABLE document_chunks ADD COLUMN embedding vector(768)');
    DB::statement('CREATE INDEX ON document_chunks USING hnsw (embedding vector_cosine_ops)');
}

    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};