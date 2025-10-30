<?php
/**
 * SAM AI Command Center - Core Class
 * 
 * Handles main business logic, orchestrates API wrappers and AI adapters
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

namespace SAM_AI_CC;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core class for SAM AI Command Center
 */
class Core {
    
    private $google_ads_wrapper;
    private $ga4_wrapper;
    private $wp_wrapper;
    private $openai_adapter;
    private $gemini_adapter;
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option('sam_ai_settings', []);
        $this->init_wrappers();
        $this->init_adapters();
        $this->init_cron_hooks();
    }
    
    /**
     * Initialize API wrappers
     */
    private function init_wrappers() {
        $this->google_ads_wrapper = new Wrapper_GoogleAds($this->settings);
        $this->ga4_wrapper = new Wrapper_GA4($this->settings);
        $this->wp_wrapper = new Wrapper_WP();
    }
    
    /**
     * Initialize AI adapters
     */
    private function init_adapters() {
        $this->openai_adapter = new Adapter_OpenAI($this->settings);
        $this->gemini_adapter = new Adapter_Gemini($this->settings);
    }
    
    /**
     * Initialize cron hooks
     */
    private function init_cron_hooks() {
        add_action('sam_ai_weekly_report', [$this, 'send_weekly_report']);
        add_action('sam_ai_monthly_report', [$this, 'send_monthly_report']);
    }
    
    /**
     * Process user query and generate insights
     *
     * @param string $query User's natural language query
     * @param string $start_date Start date for data range
     * @param string $end_date End date for data range
     * @param string $model AI model preference ('auto', 'gemini', 'gpt')
     * @return array Response data
     * @throws \Exception
     */
    public function process_query($query, $start_date, $end_date, $model = 'auto') {
        // Validate dates
        if (!$this->validate_date($start_date) || !$this->validate_date($end_date)) {
            throw new \Exception(__('Invalid date format', 'sam-ai-cc'));
        }
        
        // Check cache first
        $cache_key = $this->generate_cache_key($query, $start_date, $end_date, $model);
        $cached_result = $this->get_cached_result($cache_key);
        
        if ($cached_result !== null) {
            return [
                'cached' => true,
                'data' => $cached_result
            ];
        }
        
        // Fetch data from all sources
        $marketing_data = $this->fetch_marketing_data($start_date, $end_date);
        
        // Select AI model
        $selected_model = $this->select_ai_model($query, $model);
        
        // Build prompt
        $prompt = $this->build_prompt($query, $marketing_data);
        
        // Get AI response
        $ai_response = $this->get_ai_response($prompt, $selected_model);
        
        // Format response
        $formatted_response = $this->format_response($ai_response, $marketing_data);
        
        // Cache result
        $this->cache_result($cache_key, $formatted_response);
        
        // Log query
        $this->log_query($query, $selected_model, $start_date, $end_date);
        
        return [
            'cached' => false,
            'data' => $formatted_response,
            'model_used' => $selected_model
        ];
    }
    
    /**
     * Fetch marketing data from all sources
     *
     * @param string $start_date
     * @param string $end_date
     * @return array Combined marketing data
     */
    private function fetch_marketing_data($start_date, $end_date) {
        $data = [
            'google_ads' => [],
            'ga4' => [],
            'wordpress' => [],
            'date_range' => [
                'start' => $start_date,
                'end' => $end_date
            ]
        ];
        
        // Fetch Google Ads data with timeout handling
        try {
            $data['google_ads'] = $this->google_ads_wrapper->get_performance($start_date, $end_date);
        } catch (\Exception $e) {
            error_log('SAM AI - Google Ads Error: ' . $e->getMessage());
            $data['google_ads'] = ['error' => $e->getMessage()];
        }
        
        // Fetch GA4 data
        try {
            $data['ga4'] = $this->ga4_wrapper->get_analytics($start_date, $end_date);
        } catch (\Exception $e) {
            error_log('SAM AI - GA4 Error: ' . $e->getMessage());
            $data['ga4'] = ['error' => $e->getMessage()];
        }
        
        // Fetch WordPress data
        try {
            $data['wordpress'] = $this->wp_wrapper->get_site_data($start_date, $end_date);
        } catch (\Exception $e) {
            error_log('SAM AI - WordPress Error: ' . $e->getMessage());
            $data['wordpress'] = ['error' => $e->getMessage()];
        }
        
        return $data;
    }
    
    /**
     * Select appropriate AI model
     *
     * @param string $query User query
     * @param string $model_preference User's model preference
     * @return string Selected model ('gemini' or 'openai')
     */
    private function select_ai_model($query, $model_preference) {
        if ($model_preference !== 'auto') {
            return $model_preference === 'gemini' ? 'gemini' : 'openai';
        }
        
        // Auto-selection logic
        $analytics_keywords = ['analytics', 'data', 'metrics', 'statistics', 'numbers', 'performance'];
        $creative_keywords = ['write', 'create', 'generate', 'suggest', 'recommend', 'improve'];
        
        $query_lower = strtolower($query);
        
        foreach ($analytics_keywords as $keyword) {
            if (strpos($query_lower, $keyword) !== false) {
                return 'gemini';
            }
        }
        
        foreach ($creative_keywords as $keyword) {
            if (strpos($query_lower, $keyword) !== false) {
                return 'openai';
            }
        }
        
        // Default to Gemini for data-heavy tasks
        return 'gemini';
    }
    
    /**
     * Build AI prompt with marketing data
     *
     * @param string $query User query
     * @param array $marketing_data Marketing data
     * @return string Formatted prompt
     */
    private function build_prompt($query, $marketing_data) {
        $prompt = "You are SAM's digital marketing analyst AI assistant. Your role is to analyze marketing data and provide actionable insights.\n\n";
        
        $prompt .= "User Query: " . $query . "\n\n";
        
        $prompt .= "Marketing Data for Period: " . $marketing_data['date_range']['start'] . " to " . $marketing_data['date_range']['end'] . "\n\n";
        
        // Google Ads data
        if (!empty($marketing_data['google_ads']) && !isset($marketing_data['google_ads']['error'])) {
            $prompt .= "=== GOOGLE ADS PERFORMANCE ===\n";
            $prompt .= json_encode($marketing_data['google_ads'], JSON_PRETTY_PRINT) . "\n\n";
        }
        
        // GA4 data
        if (!empty($marketing_data['ga4']) && !isset($marketing_data['ga4']['error'])) {
            $prompt .= "=== GOOGLE ANALYTICS 4 DATA ===\n";
            $prompt .= json_encode($marketing_data['ga4'], JSON_PRETTY_PRINT) . "\n\n";
        }
        
        // WordPress data
        if (!empty($marketing_data['wordpress']) && !isset($marketing_data['wordpress']['error'])) {
            $prompt .= "=== WORDPRESS SITE DATA ===\n";
            $prompt .= json_encode($marketing_data['wordpress'], JSON_PRETTY_PRINT) . "\n\n";
        }
        
        $prompt .= "Please analyze this data and provide:\n";
        $prompt .= "1. **Summary**: Key performance highlights\n";
        $prompt .= "2. **Key Trends**: Important patterns and changes\n";
        $prompt .= "3. **Recommendations**: Actionable insights and next steps\n\n";
        $prompt .= "Format your response with clear sections and bullet points where appropriate.";
        
        return $prompt;
    }
    
    /**
     * Get AI response
     *
     * @param string $prompt
     * @param string $model
     * @return string AI response
     * @throws \Exception
     */
    private function get_ai_response($prompt, $model) {
        try {
            if ($model === 'gemini') {
                return $this->gemini_adapter->generate_content($prompt);
            } else {
                return $this->openai_adapter->generate_content($prompt);
            }
        } catch (\Exception $e) {
            error_log('SAM AI - AI Model Error: ' . $e->getMessage());
            throw new \Exception(__('AI service unavailable. Please try again later.', 'sam-ai-cc'));
        }
    }
    
    /**
     * Format response for display
     *
     * @param string $ai_response
     * @param array $marketing_data
     * @return array Formatted response
     */
    private function format_response($ai_response, $marketing_data) {
        return [
            'insights' => $ai_response,
            'raw_data' => $marketing_data,
            'timestamp' => current_time('mysql'),
            'metrics_summary' => $this->extract_key_metrics($marketing_data)
        ];
    }
    
    /**
     * Extract key metrics summary
     *
     * @param array $marketing_data
     * @return array Key metrics
     */
    private function extract_key_metrics($marketing_data) {
        $metrics = [];
        
        // Google Ads metrics
        if (!empty($marketing_data['google_ads']['summary'])) {
            $metrics['ads'] = $marketing_data['google_ads']['summary'];
        }
        
        // GA4 metrics
        if (!empty($marketing_data['ga4']['summary'])) {
            $metrics['analytics'] = $marketing_data['ga4']['summary'];
        }
        
        // WordPress metrics
        if (!empty($marketing_data['wordpress']['summary'])) {
            $metrics['wordpress'] = $marketing_data['wordpress']['summary'];
        }
        
        return $metrics;
    }
    
    /**
     * Generate cache key
     */
    private function generate_cache_key($query, $start_date, $end_date, $model) {
        return 'sam_ai_' . md5($query . $start_date . $end_date . $model);
    }
    
    /**
     * Get cached result
     */
    private function get_cached_result($cache_key) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sam_ai_cache';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT cache_value FROM $table_name WHERE cache_key = %s AND expires_at > NOW()",
            $cache_key
        ));
        
        return $result ? json_decode($result->cache_value, true) : null;
    }
    
    /**
     * Cache result
     */
    private function cache_result($cache_key, $data, $hours = 24) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sam_ai_cache';
        
        $wpdb->replace($table_name, [
            'cache_key' => $cache_key,
            'cache_value' => wp_json_encode($data),
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$hours} hours"))
        ]);
    }
    
    /**
     * Validate date format
     */
    private function validate_date($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Log query
     */
    private function log_query($query, $model, $start_date, $end_date) {
        $log_file = SAM_AI_CC_PLUGIN_DIR . 'logs/queries.log';
        $log_entry = sprintf(
            "[%s] Query: %s | Model: %s | Date Range: %s to %s\n",
            current_time('mysql'),
            $query,
            $model,
            $start_date,
            $end_date
        );
        error_log($log_entry, 3, $log_file);
    }
    
    /**
     * Send weekly report
     */
    public function send_weekly_report() {
        if (!($this->settings['enable_weekly_reports'] ?? false)) {
            return;
        }
        
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-7 days'));
        
        try {
            $result = $this->process_query(
                'Generate a comprehensive weekly marketing performance report',
                $start_date,
                $end_date,
                'auto'
            );
            
            $this->send_report($result['data'], 'Weekly Marketing Report');
        } catch (\Exception $e) {
            error_log('SAM AI - Weekly Report Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send monthly report
     */
    public function send_monthly_report() {
        if (!($this->settings['enable_monthly_reports'] ?? false)) {
            return;
        }
        
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-30 days'));
        
        try {
            $result = $this->process_query(
                'Generate a comprehensive monthly marketing performance report with trends and recommendations',
                $start_date,
                $end_date,
                'auto'
            );
            
            $this->send_report($result['data'], 'Monthly Marketing Report');
        } catch (\Exception $e) {
            error_log('SAM AI - Monthly Report Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send report via email and/or Slack
     */
    private function send_report($data, $subject) {
        // Send email
        $admin_email = get_option('admin_email');
        $message = $this->format_email_report($data);
        wp_mail($admin_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        
        // Send to Slack if configured
        if (!empty($this->settings['slack_webhook_url'])) {
            $this->send_slack_notification($data, $subject);
        }
    }
    
    /**
     * Format email report
     */
    private function format_email_report($data) {
        $html = '<html><body>';
        $html .= '<h1>SAM AI Marketing Report</h1>';
        $html .= '<div>' . nl2br($data['insights']) . '</div>';
        $html .= '<hr>';
        $html .= '<p><small>Generated by SAM AI Command Center at ' . $data['timestamp'] . '</small></p>';
        $html .= '</body></html>';
        return $html;
    }
    
    /**
     * Send Slack notification
     */
    private function send_slack_notification($data, $title) {
        $webhook_url = $this->settings['slack_webhook_url'];
        if (empty($webhook_url)) {
            return;
        }
        
        $payload = [
            'text' => $title,
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '*' . $title . '*'
                    ]
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => substr($data['insights'], 0, 3000)
                    ]
                ]
            ]
        ];
        
        wp_remote_post($webhook_url, [
            'body' => wp_json_encode($payload),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 10
        ]);
    }
}
