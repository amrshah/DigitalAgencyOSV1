<?php
/**
 * SAM AI Command Center - Gemini AI Adapter
 * 
 * Handles Google Gemini API integration
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

namespace SAM_AI_CC;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gemini AI Adapter
 */
class Adapter_Gemini {
    
    private $api_key;
    private $model = 'gemini-1.5-pro';
    private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $max_tokens = 8000;
    private $temperature = 0.7;
    
    /**
     * Constructor
     *
     * @param array $settings Plugin settings
     */
    public function __construct($settings) {
        $this->api_key = !empty($settings['gemini_api_key']) 
            ? \SAM_AI_CC\SAM_AI_Command_Center::decrypt_data($settings['gemini_api_key'])
            : '';
    }
    
    /**
     * Generate content using Gemini
     *
     * @param string $prompt User prompt
     * @param array $options Optional parameters
     * @return string Generated content
     * @throws \Exception
     */
    public function generate_content($prompt, $options = []) {
        if (empty($this->api_key)) {
            throw new \Exception(__('Gemini API key not configured', 'sam-ai-cc'));
        }
        
        // Check rate limiting
        if (!$this->check_rate_limit()) {
            throw new \Exception(__('Rate limit exceeded. Please try again later.', 'sam-ai-cc'));
        }
        
        $temperature = $options['temperature'] ?? $this->temperature;
        $max_tokens = $options['max_tokens'] ?? $this->max_tokens;
        
        // Build request body
        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $max_tokens,
                'topP' => 0.95,
                'topK' => 40
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];
        
        $url = $this->api_url . $this->model . ':generateContent?key=' . $this->api_key;
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode($body),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['error']['message'] ?? 'Unknown error';
            throw new \Exception('Gemini API Error: ' . $error_message);
        }
        
        $data = json_decode($response_body, true);
        
        // Extract generated text
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $generated_text = $data['candidates'][0]['content']['parts'][0]['text'];
            
            // Log usage
            $this->log_usage($prompt, $generated_text);
            
            // Update rate limit counter
            $this->update_rate_limit();
            
            return $generated_text;
        }
        
        // Handle blocked content
        if (isset($data['candidates'][0]['finishReason'])) {
            $reason = $data['candidates'][0]['finishReason'];
            if ($reason === 'SAFETY') {
                throw new \Exception(__('Content was blocked by safety filters', 'sam-ai-cc'));
            }
        }
        
        throw new \Exception(__('No content generated', 'sam-ai-cc'));
    }
    
    /**
     * Check rate limiting
     *
     * @return bool True if within rate limit
     */
    private function check_rate_limit() {
        $limit_key = 'sam_ai_gemini_rate_limit';
        $current_count = get_transient($limit_key);
        
        // Allow 60 requests per minute
        if ($current_count !== false && $current_count >= 60) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Update rate limit counter
     */
    private function update_rate_limit() {
        $limit_key = 'sam_ai_gemini_rate_limit';
        $current_count = get_transient($limit_key);
        
        if ($current_count === false) {
            set_transient($limit_key, 1, 60); // 60 seconds
        } else {
            set_transient($limit_key, $current_count + 1, 60);
        }
    }
    
    /**
     * Log API usage
     */
    private function log_usage($prompt, $response) {
        $log_file = SAM_AI_CC_PLUGIN_DIR . 'logs/gemini-usage.log';
        
        $log_entry = sprintf(
            "[%s] Prompt Length: %d | Response Length: %d\n",
            current_time('mysql'),
            strlen($prompt),
            strlen($response)
        );
        
        error_log($log_entry, 3, $log_file);
    }
    
    /**
     * Count tokens (approximate)
     *
     * @param string $text Text to count
     * @return int Approximate token count
     */
    public function count_tokens($text) {
        // Rough approximation: 1 token ≈ 4 characters for English
        return (int) ceil(strlen($text) / 4);
    }
    
    /**
     * Validate API key
     *
     * @return bool True if API key is valid
     */
    public function validate_api_key() {
        if (empty($this->api_key)) {
            return false;
        }
        
        try {
            $this->generate_content('Test', ['max_tokens' => 10]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get model info
     *
     * @return array Model information
     */
    public function get_model_info() {
        return [
            'name' => $this->model,
            'provider' => 'Google Gemini',
            'max_tokens' => $this->max_tokens,
            'supports_streaming' => false,
            'cost_per_1k_tokens' => 0.00025 // Approximate, check current pricing
        ];
    }
}
