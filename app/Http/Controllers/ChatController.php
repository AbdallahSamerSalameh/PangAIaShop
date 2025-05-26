<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $deepseekApiKey;
    private $deepseekApiUrl = 'https://api.deepseek.com/v1/chat/completions';

    public function __construct()
    {
        $this->deepseekApiKey = env('DEEPSEEK_API_KEY');
    }

    public function index()
    {
        return view('chat.index');
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
                'conversation' => 'sometimes|array'
            ]);

            $userMessage = $request->input('message');
            $conversation = $request->input('conversation', []);

            // Prepare conversation history
            $messages = [];
            
            // Add system message
            $messages[] = [
                'role' => 'system',
                'content' => 'You are a helpful assistant for PangAIaShop. You help customers with product inquiries, shopping assistance, and general questions about the store. Be friendly, helpful, and professional.'
            ];

            // Add conversation history
            foreach ($conversation as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }

            // Add current user message
            $messages[] = [
                'role' => 'user',
                'content' => $userMessage
            ];

            // Make API request to DeepSeek
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->deepseekApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->deepseekApiUrl, [
                'model' => 'deepseek-chat',
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7,
                'stream' => false
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['choices'][0]['message']['content'])) {
                    $botReply = $data['choices'][0]['message']['content'];
                    
                    return response()->json([
                        'success' => true,
                        'message' => $botReply,
                        'conversation' => array_merge($conversation, [
                            ['role' => 'user', 'content' => $userMessage],
                            ['role' => 'assistant', 'content' => $botReply]
                        ])
                    ]);
                } else {
                    throw new \Exception('Invalid response format from DeepSeek API');
                }
            } else {
                Log::error('DeepSeek API Error: ' . $response->body());
                throw new \Exception('Failed to get response from DeepSeek API');
            }

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            
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
}