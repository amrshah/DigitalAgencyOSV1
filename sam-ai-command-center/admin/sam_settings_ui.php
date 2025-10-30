<?php
/**
 * SAM AI Command Center - Settings UI
 * 
 * Settings page interface
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('sam_ai_settings', []);
?>

<div class="wrap sam-ai-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form id="sam-ai-settings-form" class="sam-ai-settings-form">
        <?php wp_nonce_field('sam_ai_nonce', 'sam_ai_nonce'); ?>
        
        <div class="settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#google-ads" class="nav-tab nav-tab-active"><?php _e('Google Ads', 'sam-ai-cc'); ?></a>
                <a href="#ga4" class="nav-tab"><?php _e('Google Analytics 4', 'sam-ai-cc'); ?></a>
                <a href="#ai-models" class="nav-tab"><?php _e('AI Models', 'sam-ai-cc'); ?></a>
                <a href="#automation" class="nav-tab"><?php _e('Automation', 'sam-ai-cc'); ?></a>
                <a href="#advanced" class="nav-tab"><?php _e('Advanced', 'sam-ai-cc'); ?></a>
            </nav>
            
            <!-- Google Ads Tab -->
            <div id="google-ads" class="tab-content active">
                <div class="card">
                    <h2><?php _e('Google Ads API Configuration', 'sam-ai-cc'); ?></h2>
                    <p class="description">
                        <?php _e('Follow the setup guide at', 'sam-ai-cc'); ?>
                        <a href="https://developers.google.com/google-ads/api/docs/get-started" target="_blank">
                            Google Ads API Documentation
                        </a>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="google_ads_client_id"><?php _e('Client ID', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="text" 
                                    id="google_ads_client_id" 
                                    name="google_ads_client_id" 
                                    value="<?php echo esc_attr($settings['google_ads_client_id'] ?? ''); ?>"
                                    class="regular-text"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th><label for="google_ads_client_secret"><?php _e('Client Secret', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="password" 
                                    id="google_ads_client_secret" 
                                    name="google_ads_client_secret" 
                                    value="<?php echo esc_attr($settings['google_ads_client_secret'] ?? ''); ?>"
                                    class="regular-text"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th><label for="google_ads_refresh_token"><?php _e('Refresh Token', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="text" 
                                    id="google_ads_refresh_token" 
                                    name="google_ads_refresh_token" 
                                    value="<?php echo esc_attr($settings['google_ads_refresh_token'] ?? ''); ?>"
                                    class="regular-text"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th><label for="google_ads_developer_token"><?php _e('Developer Token', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="text" 
                                    id="google_ads_developer_token" 
                                    name="google_ads_developer_token" 
                                    value="<?php echo esc_attr($settings['google_ads_developer_token'] ?? ''); ?>"
                                    class="regular-text"
                                >
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- GA4 Tab -->
            <div id="ga4" class="tab-content">
                <div class="card">
                    <h2><?php _e('Google Analytics 4 Configuration', 'sam-ai-cc'); ?></h2>
                    <p class="description">
                        <?php _e('Create a service account in Google Cloud Console and download the JSON credentials file.', 'sam-ai-cc'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="ga4_property_id"><?php _e('GA4 Property ID', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="text" 
                                    id="ga4_property_id" 
                                    name="ga4_property_id" 
                                    value="<?php echo esc_attr($settings['ga4_property_id'] ?? ''); ?>"
                                    class="regular-text"
                                    placeholder="123456789"
                                >
                                <p class="description"><?php _e('Found in GA4 Admin > Property Settings', 'sam-ai-cc'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="ga4_credentials"><?php _e('Service Account JSON', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <textarea 
                                    id="ga4_credentials" 
                                    name="ga4_credentials" 
                                    rows="10" 
                                    class="large-text code"
                                    placeholder='{"type": "service_account", ...}'
                                ><?php echo esc_textarea($settings['ga4_credentials'] ?? ''); ?></textarea>
                                <p class="description"><?php _e('Paste the entire JSON file content here', 'sam-ai-cc'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- AI Models Tab -->
            <div id="ai-models" class="tab-content">
                <div class="card">
                    <h2><?php _e('AI Model Configuration', 'sam-ai-cc'); ?></h2>
                    
                    <h3><?php _e('Google Gemini', 'sam-ai-cc'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="gemini_api_key"><?php _e('Gemini API Key', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="password" 
                                    id="gemini_api_key" 
                                    name="gemini_api_key" 
                                    value="<?php echo esc_attr($settings['gemini_api_key'] ?? ''); ?>"
                                    class="regular-text"
                                >
                                <p class="description">
                                    <?php _e('Get your API key from', 'sam-ai-cc'); ?>
                                    <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php _e('OpenAI', 'sam-ai-cc'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="openai_api_key"><?php _e('OpenAI API Key', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="password" 
                                    id="openai_api_key" 
                                    name="openai_api_key" 
                                    value="<?php echo esc_attr($settings['openai_api_key'] ?? ''); ?>"
                                    class="regular-text"
                                >
                                <p class="description">
                                    <?php _e('Get your API key from', 'sam-ai-cc'); ?>
                                    <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="notice notice-info inline">
                        <p>
                            <strong><?php _e('Note:', 'sam-ai-cc'); ?></strong>
                            <?php _e('At least one AI model API key is required for the plugin to function.', 'sam-ai-cc'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Automation Tab -->
            <div id="automation" class="tab-content">
                <div class="card">
                    <h2><?php _e('Automated Reports', 'sam-ai-cc'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Weekly Reports', 'sam-ai-cc'); ?></th>
                            <td>
                                <label>
                                    <input 
                                        type="checkbox" 
                                        name="enable_weekly_reports" 
                                        value="1"
                                        <?php checked(!empty($settings['enable_weekly_reports'])); ?>
                                    >
                                    <?php _e('Enable weekly marketing reports', 'sam-ai-cc'); ?>
                                </label>
                                <p class="description"><?php _e('Sends a weekly summary every Monday', 'sam-ai-cc'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Monthly Reports', 'sam-ai-cc'); ?></th>
                            <td>
                                <label>
                                    <input 
                                        type="checkbox" 
                                        name="enable_monthly_reports" 
                                        value="1"
                                        <?php checked(!empty($settings['enable_monthly_reports'])); ?>
                                    >
                                    <?php _e('Enable monthly marketing reports', 'sam-ai-cc'); ?>
                                </label>
                                <p class="description"><?php _e('Sends a monthly summary on the 1st of each month', 'sam-ai-cc'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="card">
                    <h2><?php _e('Slack Integration', 'sam-ai-cc'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="slack_webhook_url"><?php _e('Webhook URL', 'sam-ai-cc'); ?></label></th>
                            <td>
                                <input 
                                    type="url" 
                                    id="slack_webhook_url" 
                                    name="slack_webhook_url" 
                                    value="<?php echo esc_url($settings['slack_webhook_url'] ?? ''); ?>"
                                    class="regular-text"
                                    placeholder="https://hooks.slack.com/services/..."
                                >
                                <p class="description">
                                    <?php _e('Automated reports will be sent to this Slack channel', 'sam-ai-cc'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Advanced Tab -->
            <div id="advanced" class="tab-content">
                <div class="card">
                    <h2><?php _e('Cache Management', 'sam-ai-cc'); ?></h2>
                    
                    <p><?php _e('Query results are cached for 24 hours to reduce API costs.', 'sam-ai-cc'); ?></p>
                    
                    <button type="button" class="button" id="clear-cache">
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e('Clear All Cache', 'sam-ai-cc'); ?>
                    </button>
                </div>
                
                <div class="card">
                    <h2><?php _e('Usage Statistics', 'sam-ai-cc'); ?></h2>
                    
                    <div id="usage-stats">
                        <p><?php _e('Loading statistics...', 'sam-ai-cc'); ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <h2><?php _e('System Information', 'sam-ai-cc'); ?></h2>
                    
                    <table class="widefat">
                        <tr>
                            <td><strong><?php _e('Plugin Version', 'sam-ai-cc'); ?></strong></td>
                            <td><?php echo SAM_AI_CC_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('PHP Version', 'sam-ai-cc'); ?></strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('WordPress Version', 'sam-ai-cc'); ?></strong></td>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Cache Table Status', 'sam-ai-cc'); ?></strong></td>
                            <td>
                                <?php
                                global $wpdb;
                                $table = $wpdb->prefix . 'sam_ai_cache';
                                $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
                                echo $exists ? '<span style="color:green;">✓ ' . __('Active', 'sam-ai-cc') . '</span>' : '<span style="color:red;">✗ ' . __('Not Found', 'sam-ai-cc') . '</span>';
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary button-large">
                <?php _e('Save Settings', 'sam-ai-cc'); ?>
            </button>
        </p>
    </form>
    
    <div id="settings-message" class="notice" style="display: none;"></div>
</div>

<style>
.sam-ai-settings {
    max-width: 1200px;
}

.settings-tabs {
    margin-top: 20px;
}

.nav-tab-wrapper {
    margin-bottom: 0;
}

.tab-content {
    display: none;
    padding: 20px;
    background: white;
    border: 1px solid #ccd0d4;
    border-top: none;
}

.tab-content.active {
    display: block;
}

.tab-content .card {
    margin-bottom: 20px;
}

.tab-content h3 {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.tab-content h3:first-of-type {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-content').removeClass('active');
        $($(this).attr('href')).addClass('active');
    });
});
</script>
