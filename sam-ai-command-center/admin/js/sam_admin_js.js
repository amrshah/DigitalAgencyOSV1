/**
 * SAM AI Command Center - Admin JavaScript
 * 
 * @package SAM_AI_CC
 * @since 1.0.0
 */

(function($) {
    'use strict';

    const SamAI = {
        
        /**
         * Initialize
         */
        init() {
            this.bindEvents();
            this.loadRecentQueries();
            this.loadQuickStats();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents() {
            $('#sam-ai-query-form').on('submit', (e) => this.handleQuerySubmit(e));
            $('.quick-query-btn').on('click', (e) => this.handleQuickQuery(e));
            $('#copy-results').on('click', () => this.copyResults());
            $('#export-pdf').on('click', () => this.exportPDF());
            $('#new-query').on('click', () => this.resetForm());
            $('#sam-ai-settings-form').on('submit', (e) => this.handleSettingsSave(e));
            $('#clear-cache').on('click', () => this.clearCache());
        },
        
        /**
         * Handle query form submission
         */
        async handleQuerySubmit(e) {
            e.preventDefault();
            
            const formData = {
                action: 'sam_ai_query',
                nonce: samAiData.nonce,
                query: $('#query').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                model: $('#ai_model').val()
            };
            
            // Validate
            if (!formData.query.trim()) {
                this.showError('Please enter a question');
                return;
            }
            
            // Show loading
            this.showLoading();
            
            try {
                const response = await $.ajax({
                    url: samAiData.ajaxUrl,
                    method: 'POST',
                    data: formData,
                    timeout: 90000 // 90 seconds
                });
                
                if (response.success) {
                    this.displayResults(response.data);
                    this.saveRecentQuery(formData);
                } else {
                    this.showError(response.data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Query error:', error);
                
                if (error.statusText === 'timeout') {
                    this.showError('Request timed out. Please try a simpler query or shorter date range.');
                } else {
                    this.showError('Failed to process query. Please try again.');
                }
            } finally {
                this.hideLoading();
            }
        },
        
        /**
         * Handle quick query button click
         */
        handleQuickQuery(e) {
            const query = $(e.currentTarget).data('query');
            $('#query').val(query);
            
            // Scroll to query box
            $('html, body').animate({
                scrollTop: $('#sam-ai-query-form').offset().top - 50
            }, 500);
        },
        
        /**
         * Display results
         */
        displayResults(data) {
            const $results = $('#sam-ai-results');
            const $content = $('#results-content');
            
            // Format insights with markdown-like styling
            let formattedInsights = this.formatInsights(data.data.insights);
            
            $content.html(formattedInsights);
            
            // Update meta badges
            $('#model-used')
                .text(`Model: ${data.model_used || 'Auto'}`)
                .removeClass('cached');
            
            $('#cache-status')
                .text(data.cached ? 'Cached' : 'Fresh')
                .toggleClass('cached', data.cached);
            
            $results.slideDown();
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $results.offset().top - 50
            }, 500);
        },
        
        /**
         * Format insights text
         */
        formatInsights(text) {
            // Convert markdown-like formatting
            let formatted = text
                // Bold: **text** or __text__
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/__(.+?)__/g, '<strong>$1</strong>')
                // Italic: *text* or _text_
                .replace(/\*(.+?)\*/g, '<em>$1</em>')
                .replace(/_(.+?)_/g, '<em>$1</em>')
                // Headers: ### Header
                .replace(/^### (.+)$/gm, '<h3>$1</h3>')
                .replace(/^## (.+)$/gm, '<h2>$1</h2>')
                .replace(/^# (.+)$/gm, '<h1>$1</h1>')
                // Lists: - item or * item
                .replace(/^[•\-\*] (.+)$/gm, '<li>$1</li>')
                // Numbers: 1. item
                .replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
            
            // Wrap consecutive <li> items in <ul>
            formatted = formatted.replace(/(<li>.*?<\/li>\s*)+/g, '<ul>$&</ul>');
            
            return formatted;
        },
        
        /**
         * Show loading indicator
         */
        showLoading() {
            $('#sam-ai-results').show();
            $('#loading-indicator').show();
            $('#results-content').hide();
            $('.results-actions').hide();
            $('#sam-ai-error').hide();
            $('#submit-query').prop('disabled', true).text('Analyzing...');
        },
        
        /**
         * Hide loading indicator
         */
        hideLoading() {
            $('#loading-indicator').hide();
            $('#results-content').show();
            $('.results-actions').show();
            $('#submit-query').prop('disabled', false).html('<span class="dashicons dashicons-search"></span> Generate Insights');
        },
        
        /**
         * Show error message
         */
        showError(message) {
            $('#sam-ai-error').show().find('#error-message').text(message);
            
            setTimeout(() => {
                $('#sam-ai-error').fadeOut();
            }, 5000);
        },
        
        /**
         * Copy results to clipboard
         */
        copyResults() {
            const text = $('#results-content').text();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showSuccess('Copied to clipboard!');
                });
            } else {
                // Fallback
                const $temp = $('<textarea>').val(text).appendTo('body').select();
                document.execCommand('copy');
                $temp.remove();
                this.showSuccess('Copied to clipboard!');
            }
        },
        
        /**
         * Export as PDF (placeholder)
         */
        exportPDF() {
            alert('PDF export feature coming soon! For now, you can copy the results and paste into a document.');
        },
        
        /**
         * Reset form
         */
        resetForm() {
            $('#query').val('').focus();
            $('#sam-ai-results').slideUp();
        },
        
        /**
         * Handle settings save
         */
        async handleSettingsSave(e) {
            e.preventDefault();
            
            const formData = $('#sam-ai-settings-form').serialize() + '&action=sam_ai_save_settings';
            
            const $button = $(e.target).find('button[type="submit"]');
            const originalText = $button.text();
            $button.text('Saving...').prop('disabled', true);
            
            try {
                const response = await $.post(samAiData.ajaxUrl, formData);
                
                if (response.success) {
                    this.showSettingsMessage('Settings saved successfully!', 'success');
                } else {
                    this.showSettingsMessage(response.data.message || 'Failed to save settings', 'error');
                }
            } catch (error) {
                console.error('Settings save error:', error);
                this.showSettingsMessage('Failed to save settings', 'error');
            } finally {
                $button.text(originalText).prop('disabled', false);
            }
        },
        
        /**
         * Show settings message
         */
        showSettingsMessage(message, type) {
            const $msg = $('#settings-message');
            
            $msg.removeClass('notice-success notice-error notice-info')
                .addClass(`notice-${type}`)
                .html(`<p>${message}</p>`)
                .slideDown();
            
            setTimeout(() => {
                $msg.slideUp();
            }, 4000);
        },
        
        /**
         * Show success message
         */
        showSuccess(message) {
            const $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
            $('#sam-ai-results').before($notice);
            
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 3000);
        },
        
        /**
         * Clear cache
         */
        async clearCache() {
            if (!confirm('Are you sure you want to clear all cached queries?')) {
                return;
            }
            
            try {
                await $.post(samAiData.ajaxUrl, {
                    action: 'sam_ai_clear_cache',
                    nonce: samAiData.nonce
                });
                
                alert('Cache cleared successfully!');
            } catch (error) {
                alert('Failed to clear cache');
            }
        },
        
        /**
         * Save recent query to localStorage
         */
        saveRecentQuery(query) {
            let recent = this.getRecentQueries();
            
            recent.unshift({
                query: query.query,
                date: new Date().toISOString(),
                model: query.model
            });
            
            // Keep only last 10
            recent = recent.slice(0, 10);
            
            localStorage.setItem('sam_ai_recent_queries', JSON.stringify(recent));
            this.loadRecentQueries();
        },
        
        /**
         * Get recent queries from localStorage
         */
        getRecentQueries() {
            const stored = localStorage.getItem('sam_ai_recent_queries');
            return stored ? JSON.parse(stored) : [];
        },
        
        /**
         * Load and display recent queries
         */
        loadRecentQueries() {
            const recent = this.getRecentQueries();
            const $container = $('#recent-queries');
            
            if (recent.length === 0) {
                return;
            }
            
            let html = '<ul class="recent-queries-list">';
            
            recent.forEach((item, index) => {
                const date = new Date(item.date);
                const dateStr = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                
                html += `
                    <li>
                        <a href="#" class="recent-query-item" data-query="${this.escapeHtml(item.query)}">
                            ${this.truncate(item.query, 60)}
                        </a>
                        <small class="query-meta">${dateStr}</small>
                    </li>
                `;
            });
            
            html += '</ul>';
            
            $container.html(html);
            
            // Bind click handlers
            $('.recent-query-item').on('click', (e) => {
                e.preventDefault();
                const query = $(e.currentTarget).data('query');
                $('#query').val(query);
                $('html, body').animate({
                    scrollTop: $('#sam-ai-query-form').offset().top - 50
                }, 500);
            });
        },
        
        /**
         * Load quick stats
         */
        async loadQuickStats() {
            // This would load actual stats from the database
            // For now, showing recent queries count
            const recent = this.getRecentQueries();
            
            const thisMonth = recent.filter(q => {
                const date = new Date(q.date);
                const now = new Date();
                return date.getMonth() === now.getMonth() && 
                       date.getFullYear() === now.getFullYear();
            });
            
            $('#quick-stats').html(`
                <div class="stat-item">
                    <div class="stat-value">${recent.length}</div>
                    <div class="stat-label">Total Queries</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${thisMonth.length}</div>
                    <div class="stat-label">This Month</div>
                </div>
            `);
        },
        
        /**
         * Truncate text
         */
        truncate(text, length) {
            return text.length > length ? text.substring(0, length) + '...' : text;
        },
        
        /**
         * Escape HTML
         */
        escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    };
    
    // Initialize when document is ready
    $(document).ready(() => {
        SamAI.init();
    });

})(jQuery);
