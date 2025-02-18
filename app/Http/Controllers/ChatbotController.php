<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\ChatHistory;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            return response()->json(['error' => 'inte behörig'], 401);
        }
        $session_id = $request->session_id ?? (string) Str::uuid();
        
        $previousMessages = ChatHistory::where('user_id', $user->id)
        ->where('session_id', $session_id)
        ->orderBy('created_at', 'asc')
        ->get();

        $previousMessagesArray = $previousMessages->map(fn($chat) => [
            ['role' => 'user', 'content' => $chat->user_message],
            ['role' => 'assistant', 'content' => $chat->bot_response],
        ])->flatten(1)->toArray();
        

        $messages = array_merge($previousMessagesArray, [
            ['role' => 'user', 'content' => $request->message]
        ]);

        $response = Http::post('http://localhost:11434/api/generate', [
            'model' => 'mistral',
            'prompt' => json_encode($messages),
            'stream' => false
        ]);

        if ($response->successful()) {

            $botResponse = $response->json()['response'] ?? 'No response from LLM';

            ChatHistory::create([
                'user_id' => $user->id,
                'session_id' => $session_id,
                'user_message' => $request->message,
                'bot_response' => $botResponse,
            ]);
            return response()->json([
                'bot_response' => $botResponse,
                'session_id' => $session_id, 
            ]);

        }

        return response()->json([
            'status_code' => $response->status(),
        ], $response->status());
    }
}
