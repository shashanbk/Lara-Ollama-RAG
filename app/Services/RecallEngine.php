<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class RecallEngine
{
    public function streamAsk(string $question, callable $onChunk)
    {
        $ollamaUrl = env('OLLAMA_URL', 'http://localhost:11434');

        // 1. Get Embedding for search
        $embedding = Http::post("$ollamaUrl/api/embeddings", [
            'model' => env('OLLAMA_MODEL_EMBED'),
            'prompt' => $question,
        ])->json()['embedding'];

        $vector = '[' . implode(',', $embedding) . ']';

        // 2. Vector Search (Retrieval)
        $chunks = DB::select("
            SELECT dc.content, d.filename 
            FROM document_chunks dc
            JOIN documents d ON dc.document_id = d.id
            ORDER BY dc.embedding <=> ?::vector LIMIT 3
        ", [$vector]);

        $context = collect($chunks)->map(fn($c) => "[Source: {$c->filename}] {$c->content}")->implode("\n\n");

        // 3. Stream from Ollama
        $response = Http::withOptions(['stream' => true])->post("$ollamaUrl/api/generate", [
            'model' => env('OLLAMA_MODEL_CHAT'),
            'prompt' => "Context: $context \n\n Question: $question \n\n Answer using the context above.",
            'stream' => true
        ]);

        $body = $response->toPsrResponse()->getBody();

        // Read the stream line by line
        $buffer = '';
        while (!$body->eof()) {
            $char = $body->read(1);
            if ($char === "\n") {
                $decoded = json_decode($buffer, true);
                if (isset($decoded['response'])) {
                    $onChunk($decoded['response']); // Pass chunk to the callback
                }
                $buffer = '';
                continue;
            }
            $buffer .= $char;
        }
    }
}