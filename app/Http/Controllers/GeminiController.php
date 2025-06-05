<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gemini\Laravel\Facades\Gemini; // Import the Facade
use Google\Client as Google_Client; // You might need to import this if not already done

class GeminiController extends Controller
{
    // ... other methods

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
                'conversation' => 'sometimes|array'
            ]);

            $userMessage = $request->input('message');
            $conversation = $request->input('conversation', []);

            $conversationHistory = '';
            foreach ($conversation as $msg) {
                if ($msg['role'] === 'user') {
                    $conversationHistory .= "User: " . $msg['content'] . "\n";
                } elseif ($msg['role'] === 'assistant') {
                    $conversationHistory .= "Assistant: " . $msg['content'] . "\n";
                }
            }

            $fullPrompt = "You are a helpful AI assistant for PangAIaShop, an e-commerce website. You help customers with:
- Product inquiries and recommendations
- Shopping assistance and guidance
- General questions about the store
- Order and shipping information
- Account and website navigation help

Be friendly, helpful, and professional in your responses. Keep your answers concise but informative.

" . ($conversationHistory ? "Previous conversation:\n$conversationHistory\n" : "") . "User: $userMessage\n\nAssistant:";

            // Create Gemini client for the specified model
            $client = Gemini::generativeModel(model: 'gemini-1.5-flash-latest');

            $result = $client->generateContent($fullPrompt);

            $botReply = $result->text();

            return response()->json([
                'success' => true,
                'message' => $botReply,
                'conversation' => array_merge($conversation, [
                    ['role' => 'user', 'content' => $userMessage],
                    ['role' => 'assistant', 'content' => $botReply]
                ])
            ]);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => 'Sorry, I encountered an error. Please try again later.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function clearConversation()
    {
        return response()->json([
            'success' => true,
            'message' => 'Conversation cleared successfully'
        ]);
    }

    public function index()
    {
        return view('chat.index');
    }
}