<?php
/**
 * Plugin Name: SAM AI Command Center
 * Plugin URI: https://amrshah.github.io/
 * Description: Marketing data assistant connecting Google Ads, GA4, WordPress, and AI models (Gemini/GPT) for campaign insights.
 * Version: 1.0.0
 * Author: Ali Raza
 * Author URI: https://amrshah.github.io/
 * License: Private License
 * Text Domain: sam-ai-cc
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

namespace SAM_AI_CC;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SAM_AI_CC_VERSION', '1.0.0');
define('SAM_AI_CC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SAM_AI_CC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SAM_AI_CC_PLUGIN_FILE', __FILE__);

/**
 * PSR-4 Autoloader
 */
spl_autoload_register(function ($class) {
    $prefix = 'SAM_AI_CC\\';
    $base_dir = SAM_AI_CC_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-sam-ai-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Main Plugin Class
 */
class SAM_AI_Command_Center {
    
    private static $instance = null;
    private $core;
    
    /**
     * Singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_sam_ai_query', [$this, 'handle_ai_query']);
        add_action('wp_ajax_sam_ai_save_settings', [$this, 'save_settings']);
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once SAM_AI_CC_PLUGIN_DIR . 'includes/class-sam-ai-core.php';
        require_once SAM_AI_CC_PLUGIN_DIR . 'includes/class-sam-ai-wrapper-googleads.php';
        require_once SAM_AI_CC_PLUGIN_DIR . 'includes/class-sam-ai-wrapper-ga4.php';
        require_once SAM_AI_CC_PLUGIN_DIR . 'includes/class-sam-ai-wrapper-wp.php';
        require_once SAM_AI_CC_PLUGIN_DIR . 'includes/class-sam-ai-adapter-openai.php';
        require_once SAM_AI_CC_PLUGIN_DIR . 'includes/class-sam-ai-adapter-gemini.php';
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        $this->core = new Core();
        load_plugin_textdomain('sam-ai-cc', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        global $wpdb;
        
        // Create cache table
        $table_name = $wpdb->prefix . 'sam_ai_cache';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_value longtext NOT NULL,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key (cache_key),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create logs directory
        $logs_dir = SAM_AI_CC_PLUGIN_DIR . 'logs';
        if (!file_exists($logs_dir)) {
            wp_mkdir_p($logs_dir);
            file_put_contents($logs_dir . '/.htaccess', 'Deny from all');
        }
        
        // Schedule cron jobs
        if (!wp_next_scheduled('sam_ai_weekly_report')) {
            wp_schedule_event(time(), 'weekly', 'sam_ai_weekly_report');
        }
        if (!wp_next_scheduled('sam_ai_monthly_report')) {
            wp_schedule_event(time(), 'monthly', 'sam_ai_monthly_report');
        }
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        wp_clear_scheduled_hook('sam_ai_weekly_report');
        wp_clear_scheduled_hook('sam_ai_monthly_report');
        flush_rewrite_rules();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('SAM AI Command Center', 'sam-ai-cc'),
            __('SAM AI CC', 'sam-ai-cc'),
            'manage_options',
            'sam-ai-command-center',
            [$this, 'render_main_page'],
            'dashicons-chart-area',
            30
        );
        
        
		
add_submenu_page(
    'sam-ai-command-center',
    'Client Analytics',
    'Client Analytics',
    'manage_options',
    'sam-ai-client-analytics',
    [$this, 'render_sam_ai_client_analytics_view']
);

		//settings
		add_submenu_page(
            'sam-ai-command-center',
            __('Settings', 'sam-ai-cc'),
            __('Settings', 'sam-ai-cc'),
            'manage_options',
            'sam-ai-settings',
            [$this, 'render_settings_page']
        );

//help
add_submenu_page(
            'sam-ai-command-center',
            __('Help', 'sam-ai-cc'),
            __('Help', 'sam-ai-cc'),
            'manage_options',
            'sam-ai-help',
            [$this, 'render_help_page']
        );


    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'sam-ai-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'sam-ai-admin',
            SAM_AI_CC_PLUGIN_URL . 'admin/css/admin.css',
            [],
            SAM_AI_CC_VERSION
        );
        
        wp_enqueue_script(
            'sam-ai-admin',
            SAM_AI_CC_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery'],
            SAM_AI_CC_VERSION,
            true
        );
        
        wp_localize_script('sam-ai-admin', 'samAiData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sam_ai_nonce'),
            'strings' => [
                'processing' => __('Processing your query...', 'sam-ai-cc'),
                'error' => __('An error occurred. Please try again.', 'sam-ai-cc'),
            ]
        ]);
    }
    
    /**
     * Render main page
     */
    public function render_main_page() {
        require_once SAM_AI_CC_PLUGIN_DIR . 'admin/admin-ui.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        require_once SAM_AI_CC_PLUGIN_DIR . 'admin/settings-ui.php';
    }
    /**
     * Render help page
     */
    public function render_help_page() {
        require_once SAM_AI_CC_PLUGIN_DIR . 'admin/help-ui.php';
    }
    
    /**
     * Handle AI query via AJAX
     */
    public function handle_ai_query() {
        check_ajax_referer('sam_ai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'sam-ai-cc')]);
        }
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        $start_date = sanitize_text_field($_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days')));
        $end_date = sanitize_text_field($_POST['end_date'] ?? date('Y-m-d'));
        $model = sanitize_text_field($_POST['model'] ?? 'auto');
        
        if (empty($query)) {
            wp_send_json_error(['message' => __('Query cannot be empty', 'sam-ai-cc')]);
        }
        
        try {
            $result = $this->core->process_query($query, $start_date, $end_date, $model);
            wp_send_json_success($result);
        } catch (\Exception $e) {
            error_log('SAM AI Error: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Save settings via AJAX
     */
    public function save_settings() {
        check_ajax_referer('sam_ai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'sam-ai-cc')]);
        }
        
        $settings = [
            'google_ads_client_id' => sanitize_text_field($_POST['google_ads_client_id'] ?? ''),
            'google_ads_client_secret' => sanitize_text_field($_POST['google_ads_client_secret'] ?? ''),
            'google_ads_refresh_token' => sanitize_text_field($_POST['google_ads_refresh_token'] ?? ''),
            'google_ads_developer_token' => sanitize_text_field($_POST['google_ads_developer_token'] ?? ''),
            'ga4_property_id' => sanitize_text_field($_POST['ga4_property_id'] ?? ''),
            'ga4_credentials' => wp_unslash($_POST['ga4_credentials'] ?? ''),
            'gemini_api_key' => sanitize_text_field($_POST['gemini_api_key'] ?? ''),
            'openai_api_key' => sanitize_text_field($_POST['openai_api_key'] ?? ''),
            'enable_weekly_reports' => isset($_POST['enable_weekly_reports']),
            'enable_monthly_reports' => isset($_POST['enable_monthly_reports']),
            'slack_webhook_url' => esc_url_raw($_POST['slack_webhook_url'] ?? ''),
        ];
        
        // Encrypt sensitive data
        foreach ($settings as $key => $value) {
            if (strpos($key, 'secret') !== false || strpos($key, 'token') !== false || strpos($key, 'key') !== false) {
                $settings[$key] = $this->encrypt_data($value);
            }
        }
        
        update_option('sam_ai_settings', $settings);
        
        wp_send_json_success(['message' => __('Settings saved successfully', 'sam-ai-cc')]);
    }
    
    /**
     * Encrypt sensitive data
     */
    private function encrypt_data($data) {
        if (empty($data)) {
            return '';
        }
        
        $key = defined('SAM_AI_ENCRYPTION_KEY') ? SAM_AI_ENCRYPTION_KEY : AUTH_KEY;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decrypt_data($data) {
        if (empty($data)) {
            return '';
        }
        
        $key = defined('SAM_AI_ENCRYPTION_KEY') ? SAM_AI_ENCRYPTION_KEY : AUTH_KEY;
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }
}

// Initialize plugin
SAM_AI_Command_Center::get_instance();
