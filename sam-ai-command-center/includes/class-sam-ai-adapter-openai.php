<?php
/**
 * SAM AI Command Center - OpenAI Adapter
 * 
 * Handles OpenAI API integration (GPT-4/GPT-5)
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

namespace SAM_AI_CC;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenAI Adapter
 */
class Adapter_OpenAI {
    
    private $api_key;
    private $model = 'gpt-4-turbo-preview'; // Fallback to GPT-4 if GPT-5 not available
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $max_tokens = 4000;
    private $temperature = 0.7;
    
    /**
     * Constructor
     *
     * @param array $settings Plugin settings
     */
    public function __construct($settings) {
        $this->api_key = !empty($settings['openai_api_key']) 
            ? \SAM_AI_CC\SAM_AI_Command_Center::decrypt_data($settings['openai_api_key'])
            : '';
    }
    
    /**
     * Generate content using OpenAI
     *
     * @param string $prompt User prompt
     * @param array $options Optional parameters
     * @return string Generated content
     * @throws \Exception
     */
    public function generate_content($prompt, $options = []) {
        if (empty($this->api_key)) {
            throw new \Exception(__('OpenAI API key not configured', 'sam-ai-cc'));
        }
        
        // Check rate limiting
        if (!$this->check_rate_limit()) {
            throw new \Exception(__('Rate limit exceeded. Please try again later.', 'sam-ai-cc'));
        }
        
        $temperature = $options['temperature'] ?? $this->temperature;
        $max_tokens = $options['max_tokens'] ?? $this->max_tokens;
        $model = $options['model'] ?? $this->model;
        
        // Build messages
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are SAM\'s digital marketing analyst AI assistant. You analyze marketing data from Google Ads, Google Analytics 4, and WordPress to provide actionable insights and recommendations. Always structure your responses with clear sections: Summary, Key Trends, and Recommendations.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];
        
        // Build request body
        $body = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $max_tokens,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];
        
        $response = wp_remote_post($this->api_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
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
            
            // Handle specific error codes
            if ($response_code === 429) {
                throw new \Exception(__('Rate limit exceeded. Please try again later.', 'sam-ai-cc'));
            } elseif ($response_code === 401) {
                throw new \Exception(__('Invalid API key', 'sam-ai-cc'));
            }
            
            throw new \Exception('OpenAI API Error: ' . $error_message);
        }
        
        $data = json_decode($response_body, true);
        
        // Extract generated text
        if (isset($data['choices'][0]['message']['content'])) {
            $generated_text = $data['choices'][0]['message']['content'];
            
            // Log usage
            $this->log_usage($data['usage'] ?? []);
            
            // Update rate limit counter
            $this->update_rate_limit();
            
            return $generated_text;
        }
        
        throw new \Exception(__('No content generated', 'sam-ai-cc'));
    }
    
    /**
     * Check rate limiting
     *
     * @return bool True if within rate limit
     */
    private function check_rate_limit() {
        $limit_key = 'sam_ai_openai_rate_limit';
        $current_count = get_transient($limit_key);
        
        // Allow 50 requests per minute (conservative for free tier)
        if ($current_count !== false && $current_count >= 50) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Update rate limit counter
     */
    private function update_rate_limit() {
        $limit_key = 'sam_ai_openai_rate_limit';
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
    private function log_usage($usage) {
        $log_file = SAM_AI_CC_PLUGIN_DIR . 'logs/openai-usage.log';
        
        $log_entry = sprintf(
            "[%s] Prompt Tokens: %d | Completion Tokens: %d | Total: %d\n",
            current_time('mysql'),
            $usage['prompt_tokens'] ?? 0,
            $usage['completion_tokens'] ?? 0,
            $usage['total_tokens'] ?? 0
        );
        
        error_log($log_entry, 3, $log_file);
        
        // Store cumulative usage
        $total_usage = get_option('sam_ai_openai_total_usage', [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0
        ]);
        
        $total_usage['prompt_tokens'] += $usage['prompt_tokens'] ?? 0;
        $total_usage['completion_tokens'] += $usage['completion_tokens'] ?? 0;
        $total_usage['total_tokens'] += $usage['total_tokens'] ?? 0;
        
        update_option('sam_ai_openai_total_usage', $total_usage);
    }
    
    /**
     * Get cumulative usage
     *
     * @return array Usage statistics
     */
    public function get_usage_stats() {
        return get_option('sam_ai_openai_total_usage', [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0
        ]);
    }
    
    /**
     * Reset usage statistics
     */
    public function reset_usage_stats() {
        delete_option('sam_ai_openai_total_usage');
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
            if (strpos($e->getMessage(), 'Invalid API key') !== false) {
                return false;
            }
            // Other errors might still mean the key is valid
            return true;
        }
    }
    
    /**
     * Get available models
     *
     * @return array Available models
     */
    public function get_available_models() {
        return [
            'gpt-4-turbo-preview' => 'GPT-4 Turbo (Recommended)',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Faster, Lower Cost)'
        ];
    }
    
    /**
     * Set model
     *
     * @param string $model Model identifier
     */
    public function set_model($model) {
        $available = array_keys($this->get_available_models());
        if (in_array($model, $available)) {
            $this->model = $model;
        }
    }
    
    /**
     * Get model info
     *
     * @return array Model information
     */
    public function get_model_info() {
        $costs = [
            'gpt-4-turbo-preview' => ['input' => 0.01, 'output' => 0.03],
            'gpt-4' => ['input' => 0.03, 'output' => 0.06],
            'gpt-3.5-turbo' => ['input' => 0.0005, 'output' => 0.0015]
        ];
        
        return [
            'name' => $this->model,
            'provider' => 'OpenAI',
            'max_tokens' => $this->max_tokens,
            'supports_streaming' => true,
            'cost_per_1k_input_tokens' => $costs[$this->model]['input'] ?? 0,
            'cost_per_1k_output_tokens' => $costs[$this->model]['output'] ?? 0
        ];
    }
    
    /**
     * Calculate estimated cost
     *
     * @param int $prompt_tokens
     * @param int $completion_tokens
     * @return float Estimated cost in USD
     */
    public function calculate_cost($prompt_tokens, $completion_tokens) {
        $info = $this->get_model_info();
        
        $input_cost = ($prompt_tokens / 1000) * $info['cost_per_1k_input_tokens'];
        $output_cost = ($completion_tokens / 1000) * $info['cost_per_1k_output_tokens'];
        
        return round($input_cost + $output_cost, 4);
    }
}
