<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Gemini\Laravel\Facades\Gemini;
use Exception;

class TestGeminiCommand extends Command
{
    protected $signature = 'test:gemini';
    protected $description = 'Test Gemini API connection';

    public function handle()
    {
        $this->info('Testing Gemini API connection...');
        
        try {
            // Check if API key is configured
            $apiKey = config('gemini.api_key');
            if (empty($apiKey)) {
                $this->error('Gemini API key is not configured in config/gemini.php');
                return 1;
            }
            
            $this->info('API Key found: ' . substr($apiKey, 0, 10) . '...');
            
            // Test simple API call
            $this->info('Making test API call...');
            
            $result = Gemini::generativeModel(model: 'gemini-1.5-flash-latest')
                           ->generateContent('Hello, this is a test message. Please respond with "Test successful!"');
            
            $response = $result->text();
            
            $this->info('API Response: ' . $response);
            $this->info('✅ Gemini API test successful!');
            
            return 0;
            
        } catch (Exception $e) {
            $this->error('❌ Gemini API test failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Class: ' . get_class($e));
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            
            return 1;
        }
    }
}
