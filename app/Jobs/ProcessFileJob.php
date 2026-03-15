<?php
namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $documentId)
    {
    }

    public function handle(): void
    {
        $doc = Document::find($this->documentId);
        $content = file_get_contents($doc->path);

        $paragraphs = explode("\n\n", $content);

        foreach ($paragraphs as $index => $text) {
            $text = trim($text);
            if (empty($text))
                continue;

            // Create the chunk record
            $chunk = DocumentChunk::create([
                'document_id' => $doc->id,
                'content' => $text,
                'metadata' => [
                    'source' => $doc->filename,
                    'chunk_index' => $index,
                    'char_count' => strlen($text)
                ]
            ]);

            // Dispatch the second job to generate the embedding (the heavy lifting)
            GenerateEmbeddingJob::dispatch($chunk->id, $text);
        }
    }
}