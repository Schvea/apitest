<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $response = Http::post('http://localhost:11434/api/generate', [
            'model' => 'mistral',
            'prompt' => $request->message,
            'stream' => false
        ]);

        if ($response->successful()) {

            $data = $response->json(); 

            return response()->json([
                'message' => $data['response'] ?? 'LLM svarar ej', 
            ]);
        }

        return response()->json([
            'status_code' => $response->status(),
        ], $response->status());
    }
}
