<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Gemini\Laravel\Facades\Gemini;

class ChatController extends Controller
{
    public function __construct()
    {
        // No specific constructor needed for Gemini
    }

    public function index()
    {
        return view('chat.index');
    }

    public function sendMessage(Request $request)
    {
        try {
            // Log the incoming request
            Log::info('Chat request received', [
                'message' => $request->input('message'),
                'has_conversation' => $request->has('conversation')
            ]);

            $request->validate([
                'message' => 'required|string|max:1000',
                'conversation' => 'sometimes|array'
            ]);

            $userMessage = $request->input('message');
            $conversation = $request->input('conversation', []);

            // Log validation passed
            Log::info('Chat validation passed', [
                'user_message' => $userMessage,
                'conversation_count' => count($conversation)
            ]);

            // Build the conversation history for context
            $conversationHistory = '';
            
            // Add conversation history
            foreach ($conversation as $msg) {
                if ($msg['role'] === 'user') {
                    $conversationHistory .= "User: " . $msg['content'] . "\n";
                } elseif ($msg['role'] === 'assistant') {
                    $conversationHistory .= "Assistant: " . $msg['content'] . "\n";
                }
            }

            // Prepare the full prompt with context
            $fullPrompt = "You are a helpful AI assistant for PangAIaShop, an e-commerce website. You help customers with:
- Product inquiries and recommendations
- Shopping assistance and guidance
- General questions about the store
- Order and shipping information
- Account and website navigation help

Be friendly, helpful, and professional in your responses. Keep your answers concise but informative.

" . ($conversationHistory ? "Previous conversation:\n$conversationHistory\n" : "") . "User: $userMessage\n\nAssistant:";

            // Log before API call
            Log::info('About to call Gemini API', [
                'prompt_length' => strlen($fullPrompt),
                'api_key_set' => !empty(config('gemini.api_key'))
            ]);

            // Make API request to Gemini
            $result = Gemini::generativeModel(model: 'gemini-1.5-flash-latest')
                            ->generateContent($fullPrompt);

            $botReply = $result->text();
            
            // Log successful API call
            Log::info('Gemini API call successful', [
                'response_length' => strlen($botReply)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $botReply,
                'conversation' => array_merge($conversation, [
                    ['role' => 'user', 'content' => $userMessage],
                    ['role' => 'assistant', 'content' => $botReply]
                ])
            ]);

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Sorry, I encountered an error. Please try again later.',
                'debug' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
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
}