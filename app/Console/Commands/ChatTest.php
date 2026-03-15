<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RecallEngine; // <--- Import your new service

class ChatTest extends Command
{
    protected $signature = 'recall:chat {question}';

    public function handle(RecallEngine $engine) // Laravel injects the service here automatically
    {
        $question = $this->argument('question');
        
        $this->info("Searching documents and thinking...");
        
        $answer = $engine->ask($question);
        
        $this->info("\n--- AI ANSWER ---");
        $this->line($answer);
    }
}