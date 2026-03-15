<?php
namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Jobs\ProcessFileJob;

class IngestFiles extends Command
{
    protected $signature = 'recall:ingest {path}';
    public function handle()
    {
        $path = $this->argument('path');
        $files = \Illuminate\Support\Facades\File::allFiles($path);

        foreach ($files as $file) {
            $this->info("Queuing file: " . $file->getFilename());

            $doc = \App\Models\Document::create([
                'filename' => $file->getFilename(),
                'path' => $file->getRealPath(),
                'type' => $file->getExtension(),
            ]);

            // Hand off to the background queue
            ProcessFileJob::dispatch($doc->id);
        }

        $this->info("✅ All files added to the queue! Run 'php artisan queue:work' to process.");
    }

}