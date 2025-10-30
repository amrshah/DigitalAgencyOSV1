<?php
/**
 * SAM AI Command Center - Google Ads Wrapper
 * 
 * Handles Google Ads API integration
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

namespace SAM_AI_CC;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google Ads API Wrapper
 */
class Wrapper_GoogleAds {
    
    private $client_id;
    private $client_secret;
    private $refresh_token;
    private $developer_token;
    private $access_token;
    
    /**
     * Constructor
     *
     * @param array $settings Plugin settings
     */
    public function __construct($settings) {
        $this->client_id = $this->decrypt_setting($settings, 'google_ads_client_id');
        $this->client_secret = $this->decrypt_setting($settings, 'google_ads_client_secret');
        $this->refresh_token = $this->decrypt_setting($settings, 'google_ads_refresh_token');
        $this->developer_token = $this->decrypt_setting($settings, 'google_ads_developer_token');
    }
    
    /**
     * Decrypt setting value
     */
    private function decrypt_setting($settings, $key) {
        if (empty($settings[$key])) {
            return '';
        }
        return \SAM_AI_CC\SAM_AI_Command_Center::decrypt_data($settings[$key]);
    }
    
    /**
     * Get access token
     *
     * @return string Access token
     * @throws \Exception
     */
    private function get_access_token() {
        if ($this->access_token && !$this->is_token_expired()) {
            return $this->access_token;
        }
        
        if (empty($this->client_id) || empty($this->client_secret) || empty($this->refresh_token)) {
            throw new \Exception(__('Google Ads credentials not configured', 'sam-ai-cc'));
        }
        
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
                'grant_type' => 'refresh_token'
            ],
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            throw new \Exception($body['error_description'] ?? $body['error']);
        }
        
        $this->access_token = $body['access_token'];
        set_transient('sam_ai_google_ads_token', $this->access_token, $body['expires_in'] - 60);
        
        return $this->access_token;
    }
    
    /**
     * Check if token is expired
     */
    private function is_token_expired() {
        return get_transient('sam_ai_google_ads_token') === false;
    }
    
    /**
     * Get campaigns
     *
     * @return array Campaign data
     * @throws \Exception
     */
    public function get_campaigns() {
        if (empty($this->developer_token)) {
            return ['error' => 'Developer token not configured'];
        }
        
        try {
            $access_token = $this->get_access_token();
            
            // Note: This is a simplified implementation
            // In production, use the official Google Ads PHP Client Library
            // composer require googleads/google-ads-php
            
            $response = wp_remote_get($this->build_api_url('campaigns'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'developer-token' => $this->developer_token,
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            return $this->format_campaigns($body);
            
        } catch (\Exception $e) {
            error_log('Google Ads API Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Get performance metrics
     *
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Performance data
     * @throws \Exception
     */
    public function get_performance($start_date, $end_date) {
        if (empty($this->developer_token)) {
            return $this->get_mock_data($start_date, $end_date);
        }
        
        try {
            $access_token = $this->get_access_token();
            
            // Google Ads Query Language (GAQL)
            $query = sprintf(
                "SELECT campaign.id, campaign.name, metrics.impressions, metrics.clicks, " .
                "metrics.cost_micros, metrics.conversions, metrics.conversions_value, " .
                "metrics.ctr, metrics.average_cpc " .
                "FROM campaign " .
                "WHERE segments.date BETWEEN '%s' AND '%s' " .
                "ORDER BY metrics.impressions DESC",
                str_replace('-', '', $start_date),
                str_replace('-', '', $end_date)
            );
            
            $response = wp_remote_post($this->build_api_url('search'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'developer-token' => $this->developer_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => wp_json_encode(['query' => $query]),
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            return $this->format_performance_data($body);
            
        } catch (\Exception $e) {
            error_log('Google Ads Performance Error: ' . $e->getMessage());
            return $this->get_mock_data($start_date, $end_date);
        }
    }
    
    /**
     * Build API URL
     */
    private function build_api_url($endpoint) {
        // Replace with your customer ID
        $customer_id = get_option('sam_ai_google_ads_customer_id', '');
        return "https://googleads.googleapis.com/v16/customers/{$customer_id}/googleAds:{$endpoint}";
    }
    
    /**
     * Format campaigns data
     */
    private function format_campaigns($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']['message']];
        }
        
        $campaigns = [];
        
        if (isset($data['results'])) {
            foreach ($data['results'] as $result) {
                $campaigns[] = [
                    'id' => $result['campaign']['id'] ?? '',
                    'name' => $result['campaign']['name'] ?? '',
                    'status' => $result['campaign']['status'] ?? ''
                ];
            }
        }
        
        return ['campaigns' => $campaigns];
    }
    
    /**
     * Format performance data
     */
    private function format_performance_data($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']['message']];
        }
        
        $total_impressions = 0;
        $total_clicks = 0;
        $total_cost = 0;
        $total_conversions = 0;
        $total_conversion_value = 0;
        $campaigns = [];
        
        if (isset($data['results'])) {
            foreach ($data['results'] as $result) {
                $metrics = $result['metrics'] ?? [];
                
                $impressions = $metrics['impressions'] ?? 0;
                $clicks = $metrics['clicks'] ?? 0;
                $cost = ($metrics['costMicros'] ?? 0) / 1000000;
                $conversions = $metrics['conversions'] ?? 0;
                $conversion_value = $metrics['conversionsValue'] ?? 0;
                
                $total_impressions += $impressions;
                $total_clicks += $clicks;
                $total_cost += $cost;
                $total_conversions += $conversions;
                $total_conversion_value += $conversion_value;
                
                $campaigns[] = [
                    'name' => $result['campaign']['name'] ?? 'Unknown',
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'cost' => $cost,
                    'conversions' => $conversions,
                    'ctr' => $metrics['ctr'] ?? 0,
                    'avg_cpc' => ($metrics['averageCpc'] ?? 0) / 1000000
                ];
            }
        }
        
        return [
            'summary' => [
                'total_impressions' => $total_impressions,
                'total_clicks' => $total_clicks,
                'total_cost' => round($total_cost, 2),
                'total_conversions' => $total_conversions,
                'ctr' => $total_impressions > 0 ? round(($total_clicks / $total_impressions) * 100, 2) : 0,
                'avg_cpc' => $total_clicks > 0 ? round($total_cost / $total_clicks, 2) : 0,
                'roas' => $total_cost > 0 ? round($total_conversion_value / $total_cost, 2) : 0
            ],
            'campaigns' => $campaigns
        ];
    }
    
    /**
     * Get mock data for testing/demo
     */
    private function get_mock_data($start_date, $end_date) {
        return [
            'summary' => [
                'total_impressions' => 45320,
                'total_clicks' => 2876,
                'total_cost' => 1234.56,
                'total_conversions' => 87,
                'ctr' => 6.35,
                'avg_cpc' => 0.43,
                'roas' => 3.82
            ],
            'campaigns' => [
                [
                    'name' => 'Brand Campaign',
                    'impressions' => 23450,
                    'clicks' => 1523,
                    'cost' => 654.32,
                    'conversions' => 45,
                    'ctr' => 6.49,
                    'avg_cpc' => 0.43
                ],
                [
                    'name' => 'Product Campaign',
                    'impressions' => 21870,
                    'clicks' => 1353,
                    'cost' => 580.24,
                    'conversions' => 42,
                    'ctr' => 6.19,
                    'avg_cpc' => 0.43
                ]
            ],
            'note' => 'Mock data - Configure Google Ads API credentials for real data'
        ];
    }
}
