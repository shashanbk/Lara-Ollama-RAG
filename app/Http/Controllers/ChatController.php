<?php
namespace App\Http\Controllers;

use App\Services\RecallEngine;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function ask(Request $request, RecallEngine $engine)
    {
        $question = $request->input('question');

        // This allows us to stream the response directly to the browser
        return response()->stream(function () use ($question, $engine) {
            $engine->streamAsk($question, function ($chunk) {
                echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            });
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
        ]);
    }
}