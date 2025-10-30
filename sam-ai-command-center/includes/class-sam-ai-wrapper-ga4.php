<?php
/**
 * SAM AI Command Center - Google Analytics 4 Wrapper
 * 
 * Handles GA4 Data API integration
 *
 * @package SAM_AI_CC
 * @since 1.0.0
 */

namespace SAM_AI_CC;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * GA4 API Wrapper
 */
class Wrapper_GA4 {
    
    private $property_id;
    private $credentials;
    private $access_token;
    
    /**
     * Constructor
     *
     * @param array $settings Plugin settings
     */
    public function __construct($settings) {
        $this->property_id = $settings['ga4_property_id'] ?? '';
        $this->credentials = !empty($settings['ga4_credentials']) 
            ? json_decode($settings['ga4_credentials'], true) 
            : [];
    }
    
    /**
     * Get access token using service account
     *
     * @return string Access token
     * @throws \Exception
     */
    private function get_access_token() {
        if ($this->access_token && !$this->is_token_expired()) {
            return $this->access_token;
        }
        
        if (empty($this->credentials)) {
            throw new \Exception(__('GA4 credentials not configured', 'sam-ai-cc'));
        }
        
        // Create JWT
        $jwt = $this->create_jwt();
        
        // Exchange JWT for access token
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
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
        set_transient('sam_ai_ga4_token', $this->access_token, $body['expires_in'] - 60);
        
        return $this->access_token;
    }
    
    /**
     * Create JWT for service account authentication
     */
    private function create_jwt() {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $now = time();
        $claim = [
            'iss' => $this->credentials['client_email'] ?? '',
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];
        
        $header_encoded = $this->base64url_encode(wp_json_encode($header));
        $claim_encoded = $this->base64url_encode(wp_json_encode($claim));
        
        $signature_input = $header_encoded . '.' . $claim_encoded;
        
        $private_key = $this->credentials['private_key'] ?? '';
        openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
        
        $signature_encoded = $this->base64url_encode($signature);
        
        return $signature_input . '.' . $signature_encoded;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Check if token is expired
     */
    private function is_token_expired() {
        return get_transient('sam_ai_ga4_token') === false;
    }
    
    /**
     * Get analytics data
     *
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Analytics data
     */
    public function get_analytics($start_date, $end_date) {
        if (empty($this->property_id) || empty($this->credentials)) {
            return $this->get_mock_data($start_date, $end_date);
        }
        
        try {
            $access_token = $this->get_access_token();
            
            // Run report
            $response = wp_remote_post(
                "https://analyticsdata.googleapis.com/v1beta/properties/{$this->property_id}:runReport",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $access_token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => wp_json_encode([
                        'dateRanges' => [
                            [
                                'startDate' => $start_date,
                                'endDate' => $end_date
                            ]
                        ],
                        'dimensions' => [
                            ['name' => 'date'],
                            ['name' => 'sessionSource']
                        ],
                        'metrics' => [
                            ['name' => 'sessions'],
                            ['name' => 'totalUsers'],
                            ['name' => 'bounceRate'],
                            ['name' => 'averageSessionDuration'],
                            ['name' => 'conversions'],
                            ['name' => 'screenPageViews']
                        ]
                    ]),
                    'timeout' => 30
                ]
            );
            
            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            return $this->format_analytics_data($body);
            
        } catch (\Exception $e) {
            error_log('GA4 API Error: ' . $e->getMessage());
            return $this->get_mock_data($start_date, $end_date);
        }
    }
    
    /**
     * Format analytics data
     */
    private function format_analytics_data($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']['message']];
        }
        
        $total_sessions = 0;
        $total_users = 0;
        $total_pageviews = 0;
        $total_conversions = 0;
        $bounce_rates = [];
        $session_durations = [];
        $sources = [];
        
        if (isset($data['rows'])) {
            foreach ($data['rows'] as $row) {
                $metrics = $row['metricValues'] ?? [];
                $dimensions = $row['dimensionValues'] ?? [];
                
                $sessions = (int) ($metrics[0]['value'] ?? 0);
                $users = (int) ($metrics[1]['value'] ?? 0);
                $bounce_rate = (float) ($metrics[2]['value'] ?? 0);
                $duration = (float) ($metrics[3]['value'] ?? 0);
                $conversions = (float) ($metrics[4]['value'] ?? 0);
                $pageviews = (int) ($metrics[5]['value'] ?? 0);
                
                $source = $dimensions[1]['value'] ?? 'direct';
                
                $total_sessions += $sessions;
                $total_users += $users;
                $total_pageviews += $pageviews;
                $total_conversions += $conversions;
                $bounce_rates[] = $bounce_rate;
                $session_durations[] = $duration;
                
                if (!isset($sources[$source])) {
                    $sources[$source] = [
                        'sessions' => 0,
                        'users' => 0
                    ];
                }
                $sources[$source]['sessions'] += $sessions;
                $sources[$source]['users'] += $users;
            }
        }
        
        arsort($sources);
        
        return [
            'summary' => [
                'total_sessions' => $total_sessions,
                'total_users' => $total_users,
                'total_pageviews' => $total_pageviews,
                'total_conversions' => $total_conversions,
                'avg_bounce_rate' => !empty($bounce_rates) ? round(array_sum($bounce_rates) / count($bounce_rates), 2) : 0,
                'avg_session_duration' => !empty($session_durations) ? round(array_sum($session_durations) / count($session_durations), 2) : 0,
                'pages_per_session' => $total_sessions > 0 ? round($total_pageviews / $total_sessions, 2) : 0
            ],
            'top_sources' => array_slice($sources, 0, 10, true)
        ];
    }
    
    /**
     * Get mock data for testing
     */
    private function get_mock_data($start_date, $end_date) {
        return [
            'summary' => [
                'total_sessions' => 12543,
                'total_users' => 9876,
                'total_pageviews' => 34567,
                'total_conversions' => 234,
                'avg_bounce_rate' => 42.35,
                'avg_session_duration' => 145.67,
                'pages_per_session' => 2.76
            ],
            'top_sources' => [
                'google' => ['sessions' => 5432, 'users' => 4321],
                'direct' => ['sessions' => 3456, 'users' => 2876],
                'facebook' => ['sessions' => 1876, 'users' => 1543],
                'twitter' => ['sessions' => 987, 'users' => 789],
                'linkedin' => ['sessions' => 792, 'users' => 654]
            ],
            'note' => 'Mock data - Configure GA4 credentials for real data'
        ];
    }
}
