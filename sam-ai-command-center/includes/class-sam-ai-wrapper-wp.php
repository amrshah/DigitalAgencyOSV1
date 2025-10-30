<?php
/**
 * SAM AI Command Center - WordPress Data Wrapper
 * 
 * Handles WordPress site data retrieval
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

namespace SAM_AI_CC;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WordPress Data Wrapper
 */
class Wrapper_WP {
    
    /**
     * Get site data
     *
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Site data
     */
    public function get_site_data($start_date, $end_date) {
        return [
            'summary' => $this->get_summary($start_date, $end_date),
            'posts' => $this->get_posts_data($start_date, $end_date),
            'forms' => $this->get_form_submissions($start_date, $end_date),
            'comments' => $this->get_comments_data($start_date, $end_date)
        ];
    }
    
    /**
     * Get summary statistics
     */
    private function get_summary($start_date, $end_date) {
        return [
            'total_posts' => $this->count_posts($start_date, $end_date),
            'total_pages' => $this->count_pages(),
            'total_users' => count_users()['total_users'],
            'total_comments' => $this->count_comments($start_date, $end_date),
            'site_url' => get_site_url(),
            'site_name' => get_bloginfo('name')
        ];
    }
    
    /**
     * Count posts in date range
     */
    private function count_posts($start_date, $end_date) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'post' 
            AND post_status = 'publish' 
            AND post_date >= %s 
            AND post_date <= %s",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));
        
        return (int) $count;
    }
    
    /**
     * Count pages
     */
    private function count_pages() {
        $pages = wp_count_posts('page');
        return (int) ($pages->publish ?? 0);
    }
    
    /**
     * Count comments in date range
     */
    private function count_comments($start_date, $end_date) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->comments} 
            WHERE comment_approved = '1' 
            AND comment_date >= %s 
            AND comment_date <= %s",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));
        
        return (int) $count;
    }
    
    /**
     * Get posts data with performance metrics
     */
    private function get_posts_data($start_date, $end_date) {
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'date_query' => [
                [
                    'after' => $start_date,
                    'before' => $end_date,
                    'inclusive' => true
                ]
            ]
        ];
        
        $posts = get_posts($args);
        $posts_data = [];
        
        foreach ($posts as $post) {
            $posts_data[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'date' => $post->post_date,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'categories' => $this->get_post_categories($post->ID),
                'comments_count' => get_comments_number($post->ID),
                'views' => $this->get_post_views($post->ID)
            ];
        }
        
        return $posts_data;
    }
    
    /**
     * Get post categories
     */
    private function get_post_categories($post_id) {
        $categories = get_the_category($post_id);
        return array_map(function($cat) {
            return $cat->name;
        }, $categories);
    }
    
    /**
     * Get post views (from popular plugins or custom meta)
     */
    private function get_post_views($post_id) {
        // Check for popular view count plugins
        
        // Jetpack Stats
        if (function_exists('stats_get_csv')) {
            $stats = stats_get_csv('postviews', ['post_id' => $post_id]);
            if (!empty($stats[0]['views'])) {
                return (int) $stats[0]['views'];
            }
        }
        
        // WP-PostViews
        if (function_exists('get_post_views')) {
            return (int) get_post_views($post_id);
        }
        
        // Post Views Counter
        if (function_exists('pvc_get_post_views')) {
            return (int) pvc_get_post_views($post_id);
        }
        
        // Custom meta key (common convention)
        $views = get_post_meta($post_id, 'post_views_count', true);
        if ($views) {
            return (int) $views;
        }
        
        return 0;
    }
    
    /**
     * Get form submissions (Fluent Forms integration)
     */
    private function get_form_submissions($start_date, $end_date) {
        global $wpdb;
        
        // Check if Fluent Forms is active
        if (!defined('FLUENTFORM')) {
            return [
                'note' => 'Fluent Forms not installed',
                'submissions' => []
            ];
        }
        
        $table_name = $wpdb->prefix . 'fluentform_submissions';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            return [
                'note' => 'Fluent Forms table not found',
                'submissions' => []
            ];
        }
        
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT form_id, COUNT(*) as count, status 
            FROM {$table_name} 
            WHERE created_at >= %s 
            AND created_at <= %s 
            GROUP BY form_id, status",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ), ARRAY_A);
        
        $total_submissions = 0;
        $forms_data = [];
        
        foreach ($submissions as $submission) {
            $form_id = $submission['form_id'];
            $count = (int) $submission['count'];
            $total_submissions += $count;
            
            if (!isset($forms_data[$form_id])) {
                $forms_data[$form_id] = [
                    'form_id' => $form_id,
                    'form_title' => $this->get_form_title($form_id),
                    'total' => 0,
                    'by_status' => []
                ];
            }
            
            $forms_data[$form_id]['total'] += $count;
            $forms_data[$form_id]['by_status'][$submission['status']] = $count;
        }
        
        return [
            'total_submissions' => $total_submissions,
            'forms' => array_values($forms_data)
        ];
    }
    
    /**
     * Get form title from Fluent Forms
     */
    private function get_form_title($form_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fluentform_forms';
        
        $title = $wpdb->get_var($wpdb->prepare(
            "SELECT title FROM {$table_name} WHERE id = %d",
            $form_id
        ));
        
        return $title ?: 'Form #' . $form_id;
    }
    
    /**
     * Get comments data
     */
    private function get_comments_data($start_date, $end_date) {
        $args = [
            'status' => 'approve',
            'date_query' => [
                [
                    'after' => $start_date,
                    'before' => $end_date,
                    'inclusive' => true
                ]
            ],
            'number' => 10,
            'orderby' => 'comment_date',
            'order' => 'DESC'
        ];
        
        $comments = get_comments($args);
        $comments_data = [];
        
        foreach ($comments as $comment) {
            $comments_data[] = [
                'id' => $comment->comment_ID,
                'post_title' => get_the_title($comment->comment_post_ID),
                'author' => $comment->comment_author,
                'date' => $comment->comment_date,
                'content' => wp_trim_words($comment->comment_content, 20)
            ];
        }
        
        return $comments_data;
    }
    
    /**
     * Get traffic data from MonsterInsights if available
     */
    public function get_traffic_data($start_date, $end_date) {
        // Check for MonsterInsights
        if (!class_exists('MonsterInsights_Lite') && !class_exists('MonsterInsights')) {
            return ['note' => 'MonsterInsights not installed'];
        }
        
        // MonsterInsights API integration would go here
        // This is a placeholder for the actual implementation
        
        return [
            'note' => 'MonsterInsights integration available',
            'data' => []
        ];
    }
}
