<?php
/**
 * SAM AI Command Center - Admin UI
 * 
 * Main dashboard interface
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap sam-ai-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="sam-ai-container">
        <div class="sam-ai-main-panel">
            <div class="card">
                <h2><?php _e('Marketing Insights Query', 'sam-ai-cc'); ?></h2>
                
                <form id="sam-ai-query-form" class="sam-ai-form">
                    <?php wp_nonce_field('sam_ai_nonce', 'sam_ai_nonce'); ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date"><?php _e('Start Date', 'sam-ai-cc'); ?></label>
                            <input 
                                type="date" 
                                id="start_date" 
                                name="start_date" 
                                value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>" 
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date"><?php _e('End Date', 'sam-ai-cc'); ?></label>
                            <input 
                                type="date" 
                                id="end_date" 
                                name="end_date" 
                                value="<?php echo date('Y-m-d'); ?>" 
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="ai_model"><?php _e('AI Model', 'sam-ai-cc'); ?></label>
                            <select id="ai_model" name="ai_model">
                                <option value="auto"><?php _e('Auto Select', 'sam-ai-cc'); ?></option>
                                <option value="gemini"><?php _e('Google Gemini', 'sam-ai-cc'); ?></option>
                                <option value="openai"><?php _e('OpenAI GPT', 'sam-ai-cc'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="query"><?php _e('Your Question', 'sam-ai-cc'); ?></label>
                        <textarea 
                            id="query" 
                            name="query" 
                            rows="4" 
                            placeholder="<?php esc_attr_e('e.g., What were my top performing campaigns this month? How can I improve my conversion rate?', 'sam-ai-cc'); ?>"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="quick-queries">
                        <p><strong><?php _e('Quick Queries:', 'sam-ai-cc'); ?></strong></p>
                        <button type="button" class="button quick-query-btn" data-query="Summarize my Google Ads performance and suggest optimizations">
                            <?php _e('Ads Performance Summary', 'sam-ai-cc'); ?>
                        </button>
                        <button type="button" class="button quick-query-btn" data-query="What are my top traffic sources and how are they converting?">
                            <?php _e('Traffic Analysis', 'sam-ai-cc'); ?>
                        </button>
                        <button type="button" class="button quick-query-btn" data-query="Compare this period with the previous 30 days">
                            <?php _e('Period Comparison', 'sam-ai-cc'); ?>
                        </button>
                        <button type="button" class="button quick-query-btn" data-query="Analyze my content performance and suggest topics">
                            <?php _e('Content Insights', 'sam-ai-cc'); ?>
                        </button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button button-primary button-large" id="submit-query">
                            <span class="dashicons dashicons-search"></span>
                            <?php _e('Generate Insights', 'sam-ai-cc'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <div id="sam-ai-results" class="card sam-ai-results" style="display: none;">
                <div class="results-header">
                    <h2><?php _e('AI Insights', 'sam-ai-cc'); ?></h2>
                    <div class="results-meta">
                        <span id="model-used" class="badge"></span>
                        <span id="cache-status" class="badge"></span>
                    </div>
                </div>
                
                <div id="loading-indicator" class="loading-indicator">
                    <div class="spinner is-active"></div>
                    <p><?php _e('Analyzing your marketing data...', 'sam-ai-cc'); ?></p>
                </div>
                
                <div id="results-content" class="results-content"></div>
                
                <div class="results-actions">
                    <button type="button" class="button" id="copy-results">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php _e('Copy to Clipboard', 'sam-ai-cc'); ?>
                    </button>
                    <button type="button" class="button" id="export-pdf">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export as PDF', 'sam-ai-cc'); ?>
                    </button>
                    <button type="button" class="button" id="new-query">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('New Query', 'sam-ai-cc'); ?>
                    </button>
                </div>
            </div>
            
            <div id="sam-ai-error" class="notice notice-error" style="display: none;">
                <p id="error-message"></p>
            </div>
        </div>
        
        <div class="sam-ai-sidebar">
            <div class="card">
                <h3><?php _e('Quick Stats', 'sam-ai-cc'); ?></h3>
                <div class="stats-grid" id="quick-stats">
                    <div class="stat-item">
                        <div class="stat-value">-</div>
                        <div class="stat-label"><?php _e('Total Queries', 'sam-ai-cc'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">-</div>
                        <div class="stat-label"><?php _e('This Month', 'sam-ai-cc'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h3><?php _e('Recent Queries', 'sam-ai-cc'); ?></h3>
                <div id="recent-queries" class="recent-queries">
                    <p class="description"><?php _e('Your recent queries will appear here', 'sam-ai-cc'); ?></p>
                </div>
            </div>
            
            <div class="card">
                <h3><?php _e('Tips & Best Practices', 'sam-ai-cc'); ?></h3>
                <ul class="tips-list">
                    <li><?php _e('Be specific in your questions for better insights', 'sam-ai-cc'); ?></li>
                    <li><?php _e('Compare time periods to identify trends', 'sam-ai-cc'); ?></li>
                    <li><?php _e('Use the quick queries for common reports', 'sam-ai-cc'); ?></li>
                    <li><?php _e('Results are cached for 24 hours to save API costs', 'sam-ai-cc'); ?></li>
                </ul>
            </div>
            
            <div class="card">
                <h3><?php _e('Need Help?', 'sam-ai-cc'); ?></h3>
                <p class="description">
                    <?php _e('Configure your API credentials in', 'sam-ai-cc'); ?>
                    <a href="<?php echo admin_url('admin.php?page=sam-ai-settings'); ?>">
                        <?php _e('Settings', 'sam-ai-cc'); ?>
                    </a>
                </p>
                <p class="description">
                    <?php _e('Make sure Google Ads, GA4, and AI model credentials are properly configured.', 'sam-ai-cc'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.sam-ai-dashboard {
    margin-right: 20px;
}

.sam-ai-container {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
    margin-top: 20px;
}

.sam-ai-main-panel {
    min-width: 0;
}

.sam-ai-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sam-ai-form .form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 5px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px;
}

.quick-queries {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.quick-query-btn {
    margin: 5px 5px 5px 0;
}

.form-actions {
    margin-top: 20px;
}

.sam-ai-results {
    margin-top: 20px;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.results-meta {
    display: flex;
    gap: 10px;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    background: #0073aa;
    color: white;
    border-radius: 3px;
    font-size: 12px;
}

.badge.cached {
    background: #46b450;
}

.loading-indicator {
    text-align: center;
    padding: 40px 20px;
}

.results-content {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
    line-height: 1.8;
    white-space: pre-wrap;
}

.results-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.recent-queries {
    max-height: 300px;
    overflow-y: auto;
}

.tips-list {
    padding-left: 20px;
    line-height: 1.8;
}

@media (max-width: 1200px) {
    .sam-ai-container {
        grid-template-columns: 1fr;
    }
    
    .sam-ai-form .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
