
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
