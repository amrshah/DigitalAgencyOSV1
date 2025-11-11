# SAM AI Command Center

**Version:** 1.0.0  
**Requires at least:** WordPress 6.0  
**Tested up to:** WordPress 6.4  
**Requires PHP:** 8.0+  
**License:** GPL v2 or later

A powerful WordPress plugin that connects Google Ads, Google Analytics 4, WordPress data, and AI models (Gemini and GPT) to generate comprehensive marketing insights directly in your WordPress admin dashboard.

---

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Usage](#usage)
5. [API Cost Estimation](#api-cost-estimation)
6. [Troubleshooting](#troubleshooting)
7. [Developer Guide](#developer-guide)
8. [Changelog](#changelog)

---

## Features

### Core Features
- **Natural Language Queries**: Ask questions in plain English about your marketing performance
- **Multi-Source Data Integration**: Connects Google Ads, GA4, and WordPress data
- **Dual AI Models**: Support for both Google Gemini and OpenAI GPT models
- **Intelligent Model Selection**: Automatically chooses the best AI model based on query type
- **Smart Caching**: 24-hour result caching to minimize API costs
- **Automated Reports**: Schedule weekly and monthly marketing summaries
- **Slack Integration**: Send reports directly to your Slack workspace

### Data Sources
1. **Google Ads**: Campaign performance, CTR, CPC, ROAS, conversions
2. **Google Analytics 4**: Sessions, users, bounce rate, traffic sources
3. **WordPress**: Post performance, form submissions (Fluent Forms), comments

### Security Features
- Encrypted storage of API credentials
- WordPress nonce verification
- Role-based access control (admin only)
- Rate limiting to prevent abuse
- Secure AJAX endpoints

---

## Installation

### Method 1: Manual Installation

1. Download the plugin files and extract them
2. Upload the `sam-ai-command-center` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **SAM AI CC** in the admin menu

### Method 2: Upload ZIP

1. Download the plugin as a ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin

### Post-Installation

After activation, the plugin will:
- Create a cache table (`wp_sam_ai_cache`)
- Create a logs directory
- Schedule automated report cron jobs

---

## Configuration

### 1. Google Ads API Setup

#### Prerequisites
- Google Ads account
- Google Cloud Console project
- Google Ads API enabled

#### Steps

1. **Create Google Cloud Project**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project or select existing
   - Enable the Google Ads API

2. **Create OAuth 2.0 Credentials**
   - Go to **APIs & Services > Credentials**
   - Click **Create Credentials > OAuth 2.0 Client ID**
   - Choose **Web application**
   - Add authorized redirect URIs (your site URL)
   - Save Client ID and Client Secret

3. **Get Developer Token**
   - Go to [Google Ads](https://ads.google.com/)
   - Click **Tools & Settings > Setup > API Center**
   - Apply for developer token (may require approval)

4. **Get Refresh Token**
   - Use the [OAuth 2.0 Playground](https://developers.google.com/oauthplayground/)
   - Configure with your Client ID and Secret
   - Authorize Google Ads API scope
   - Exchange authorization code for refresh token

5. **Configure in Plugin**
   - Navigate to **SAM AI CC > Settings > Google Ads**
   - Enter Client ID, Client Secret, Refresh Token, and Developer Token
   - Save settings

### 2. Google Analytics 4 Setup

#### Prerequisites
- GA4 property configured
- Google Cloud Console access

#### Steps

1. **Create Service Account**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Navigate to **IAM & Admin > Service Accounts**
   - Click **Create Service Account**
   - Name it (e.g., "SAM AI Analytics")
   - Grant **Viewer** role
   - Create and download JSON key

2. **Grant Access to GA4**
   - Go to [Google Analytics](https://analytics.google.com/)
   - Navigate to **Admin > Property > Property Access Management**
   - Add the service account email (from JSON file)
   - Grant **Viewer** permissions

3. **Get Property ID**
   - In GA4, go to **Admin > Property Settings**
   - Copy the Property ID (numeric value)

4. **Configure in Plugin**
   - Navigate to **SAM AI CC > Settings > Google Analytics 4**
   - Enter Property ID
   - Paste the entire JSON file content into Service Account JSON field
   - Save settings

### 3. AI Model Configuration

#### Google Gemini

1. **Get API Key**
   - Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
   - Sign in with your Google account
   - Click **Create API Key**
   - Copy the API key

2. **Configure in Plugin**
   - Navigate to **SAM AI CC > Settings > AI Models**
   - Paste API key in Gemini API Key field
   - Save settings

#### OpenAI GPT

1. **Get API Key**
   - Go to [OpenAI Platform](https://platform.openai.com/api-keys)
   - Sign in or create account
   - Click **Create new secret key**
   - Copy the API key immediately (won't be shown again)

2. **Configure in Plugin**
   - Navigate to **SAM AI CC > Settings > AI Models**
   - Paste API key in OpenAI API Key field
   - Save settings

### 4. Optional: Automation Setup

#### Enable Automated Reports

1. Navigate to **SAM AI CC > Settings > Automation**
2. Check **Enable weekly reports** and/or **Enable monthly reports**
3. Reports will be sent to the WordPress admin email

#### Slack Integration

1. **Create Slack Webhook**
   - Go to your [Slack workspace settings](https://api.slack.com/messaging/webhooks)
   - Create an Incoming Webhook
   - Choose the channel for notifications
   - Copy the Webhook URL

2. **Configure in Plugin**
   - Navigate to **SAM AI CC > Settings > Automation**
   - Paste Webhook URL in Slack Webhook URL field
   - Save settings

---

## Usage

### Basic Query

1. Go to **SAM AI CC** in WordPress admin
2. Select date range (Start Date and End Date)
3. Choose AI model (or leave as "Auto Select")
4. Enter your question, for example:
   - "What were my top performing campaigns this month?"
   - "How can I improve my conversion rate?"
   - "Compare this period with last month"
5. Click **Generate Insights**

### Quick Queries

Use pre-made query buttons for common reports:
- **Ads Performance Summary**: Get overview of Google Ads metrics
- **Traffic Analysis**: Analyze traffic sources and conversions
- **Period Comparison**: Compare current vs previous period
- **Content Insights**: Review content performance

### Viewing Results

Results are displayed with three sections:
1. **Summary**: Key highlights and overall performance
2. **Key Trends**: Important patterns and changes
3. **Recommendations**: Actionable insights and next steps

### Exporting Results

- **Copy to Clipboard**: Click copy button to save insights
- **Export as PDF**: Generate PDF report (coming soon)

### Recent Queries

Your last 10 queries are saved in the sidebar. Click any to rerun it.

---

## API Cost Estimation

### Google Gemini

- **Free Tier**: 60 requests/minute
- **Pricing** (if exceeding free tier): ~$0.00025 per 1K tokens
- **Average Query Cost**: $0.002 - $0.01

### OpenAI GPT-4

- **No Free Tier**
- **Pricing**: 
  - Input: $0.01 per 1K tokens
  - Output: $0.03 per 1K tokens
- **Average Query Cost**: $0.05 - $0.15

### Cost Reduction Strategies

1. **Use Caching**: Results cached for 24 hours (enabled by default)
2. **Choose Gemini**: More cost-effective for data analysis
3. **Shorter Date Ranges**: Less data = lower token usage
4. **Specific Questions**: Focused queries use fewer tokens

### Monthly Cost Estimate

**Light Usage** (10 queries/day with caching):
- Gemini: $1-3/month
- GPT-4: $15-30/month

**Heavy Usage** (50 queries/day):
- Gemini: $5-10/month
- GPT-4: $50-100/month

---

## Troubleshooting

### Common Issues

#### "Google Ads credentials not configured"
- **Solution**: Verify all four credentials are entered in Settings > Google Ads
- Check for extra spaces or missing characters

#### "GA4 credentials not configured"
- **Solution**: Ensure JSON is valid (use JSON validator)
- Verify service account has access to GA4 property

#### "AI service unavailable"
- **Solution**: Check API key is correct
- Verify you haven't exceeded rate limits
- Check API service status pages

#### "Rate limit exceeded"
- **Solution**: Wait 60 seconds and try again
- Consider upgrading API plan

#### Results showing "Mock data"
- **Solution**: API credentials not configured correctly
- Plugin falls back to demo data until real credentials added

### Debug Mode

Enable WordPress debug mode to see detailed error logs:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs at: `wp-content/debug.log`

### Plugin Logs

Plugin-specific logs stored in:
- `wp-content/plugins/sam-ai-command-center/logs/queries.log`
- `wp-content/plugins/sam-ai-command-center/logs/gemini-usage.log`
- `wp-content/plugins/sam-ai-command-center/logs/openai-usage.log`

### Clear Cache

If results seem outdated:
1. Go to **SAM AI CC > Settings > Advanced**
2. Click **Clear All Cache**

---

## Developer Guide

### Extending the Plugin

#### Add Custom Data Source

```php
// Create new wrapper class
namespace SAM_AI_CC;

class Wrapper_Custom {
    public function get_data($start_date, $end_date) {
        // Fetch your custom data
        return [
            'summary' => [...],
            'details' => [...]
        ];
    }
}
```

#### Custom AI Prompt

```php
add_filter('sam_ai_prompt', function($prompt, $query, $data) {
    // Modify prompt before sending to AI
    $custom_context = "Additional context...";
    return $prompt . "\n" . $custom_context;
}, 10, 3);
```

#### Add New AI Adapter

```php
namespace SAM_AI_CC;

class Adapter_Custom {
    public function generate_content($prompt, $options = []) {
        // Implement your AI integration
        return $response;
    }
}
```

### Hooks & Filters

#### Actions

- `sam_ai_before_query`: Before processing query
- `sam_ai_after_query`: After query completion
- `sam_ai_weekly_report`: Weekly report generation
- `sam_ai_monthly_report`: Monthly report generation

#### Filters

- `sam_ai_prompt`: Modify AI prompt
- `sam_ai_response`: Modify AI response
- `sam_ai_cache_duration`: Change cache time (default: 24 hours)
- `sam_ai_rate_limit`: Adjust rate limiting

### Database Schema

#### Cache Table

```sql
CREATE TABLE wp_sam_ai_cache (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    cache_key varchar(255) NOT NULL,
    cache_value longtext NOT NULL,
    expires_at datetime NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY cache_key (cache_key),
    KEY expires_at (expires_at)
);
```

---

## Changelog

### Version 1.0.0 (2024-10-30)

#### Initial Release
- ✅ Google Ads integration
- ✅ Google Analytics 4 integration
- ✅ WordPress data wrapper
- ✅ Google Gemini adapter
- ✅ OpenAI GPT adapter
- ✅ Admin dashboard UI
- ✅ Settings page
- ✅ Query caching system
- ✅ Automated reports (weekly/monthly)
- ✅ Slack integration
- ✅ Security features (encryption, nonces, rate limiting)
- ✅ Responsive design
- ✅ Comprehensive documentation

---

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## Support

For issues, questions, or feature requests:
- Create an issue on GitHub
- Email: support@yourcompany.com
- Documentation: https://yourcompany.com/docs/sam-ai

---

## Credits

- Google Ads API
- Google Analytics 4 Data API
- Google Gemini AI
- OpenAI GPT
- WordPress Community

---

**Built with ❤️ for marketers who want data-driven insights**
