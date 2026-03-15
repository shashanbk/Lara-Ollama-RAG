<?php
namespace App\Jobs;

use App\Models\DocumentChunk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $chunkId, public string $text) {}

    public function handle(): void
    {
        $ollamaUrl = env('OLLAMA_URL', 'http://localhost:11434');
        $model = env('OLLAMA_MODEL_EMBED', 'nomic-embed-text');

        $response = Http::post("$ollamaUrl/api/embeddings", [
            'model' => $model,
            'prompt' => $this->text,
        ]);

        if ($response->successful()) {
            $vector = '[' . implode(',', $response->json()['embedding']) . ']';
            
            // Scalable SQL update for pgvector
            DB::statement("UPDATE document_chunks SET embedding = ? WHERE id = ?", [
                $vector, 
                $this->chunkId
            ]);
        }
    }
}