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
}?>
<div class="wrap sam-ai-settings">
    <div class="card">
    <h1>Help</H1>
    <h2>Configuration</h2>
    <h3>1. Google Ads API Setup</h3>
    <h4>Prerequisites</h4>
    <ul>
        <li>Google Ads account</li>
        <li>Google Cloud Console project</li>
        <li>Google Ads API enabled</li>
    </ul>
    <h4>Steps</h4>
    <ol>
        <li>
            <p><strong>Create Google Cloud Project</strong></p>
            <ul>
                <li>Go to <a href="https://console.cloud.google.com/">Google Cloud Console</a></li>
                <li>Create a new project or select existing</li>
                <li>Enable the Google Ads API</li>
            </ul>
        </li>
        <li>
            <p><strong>Create OAuth 2.0 Credentials</strong></p>
            <ul>
                <li>Go to <strong>APIs &amp; Services &gt; Credentials</strong></li>
                <li>Click <strong>Create Credentials &gt; OAuth 2.0 Client ID</strong></li>
                <li>Choose <strong>Web application</strong></li>
                <li>Add authorized redirect URIs (your site URL)</li>
                <li>Save Client ID and Client Secret</li>
            </ul>
        </li>
        <li>
            <p><strong>Get Developer Token</strong></p>
            <ul>
                <li>Go to <a href="https://ads.google.com/">Google Ads</a></li>
                <li>Click <strong>Tools &amp; Settings &gt; Setup &gt; API Center</strong></li>
                <li>Apply for developer token (may require approval)</li>
            </ul>
        </li>
        <li>
            <p><strong>Get Refresh Token</strong></p>
            <ul>
                <li>Use the <a href="https://developers.google.com/oauthplayground/">OAuth 2.0 Playground</a></li>
                <li>Configure with your Client ID and Secret</li>
                <li>Authorize Google Ads API scope</li>
                <li>Exchange authorization code for refresh token</li>
            </ul>
        </li>
        <li>
            <p><strong>Configure in Plugin</strong></p>
            <ul>
                <li>Navigate to <strong>SAM AI CC &gt; Settings &gt; Google Ads</strong></li>
                <li>Enter Client ID, Client Secret, Refresh Token, and Developer Token</li>
                <li>Save settings</li>
            </ul>
        </li>
    </ol>
    <h3>2. Google Analytics 4 Setup</h3>
    <h4>Prerequisites</h4>
    <ul>
        <li>GA4 property configured</li>
        <li>Google Cloud Console access</li>
    </ul>
    <h4>Steps</h4>
    <ol>
        <li>
            <p><strong>Create Service Account</strong></p>
            <ul>
                <li>Go to <a href="https://console.cloud.google.com/">Google Cloud Console</a></li>
                <li>Navigate to <strong>IAM &amp; Admin &gt; Service Accounts</strong></li>
                <li>Click <strong>Create Service Account</strong></li>
                <li>Name it (e.g., "SAM AI Analytics")</li>
                <li>Grant <strong>Viewer</strong> role</li>
                <li>Create and download JSON key</li>
            </ul>
        </li>
        <li>
            <p><strong>Grant Access to GA4</strong></p>
            <ul>
                <li>Go to <a href="https://analytics.google.com/">Google Analytics</a></li>
                <li>Navigate to <strong>Admin &gt; Property &gt; Property Access Management</strong></li>
                <li>Add the service account email (from JSON file)</li>
                <li>Grant <strong>Viewer</strong> permissions</li>
            </ul>
        </li>
        <li>
            <p><strong>Get Property ID</strong></p>
            <ul>
                <li>In GA4, go to <strong>Admin &gt; Property Settings</strong></li>
                <li>Copy the Property ID (numeric value)</li>
            </ul>
        </li>
        <li>
            <p><strong>Configure in Plugin</strong></p>
            <ul>
                <li>Navigate to <strong>SAM AI CC &gt; Settings &gt; Google Analytics 4</strong></li>
                <li>Enter Property ID</li>
                <li>Paste the entire JSON file content into Service Account JSON field</li>
                <li>Save settings</li>
            </ul>
        </li>
    </ol>
    <!-- <h3>3. AI Model Configuration</h3>
    <h4>Google Gemini</h4>
    <ol>
        <li>
            <p><strong>Get API Key</strong></p>
            <ul>
                <li>Go to <a href="https://makersuite.google.com/app/apikey">Google AI Studio</a></li>
                <li>Sign in with your Google account</li>
                <li>Click <strong>Create API Key</strong></li>
                <li>Copy the API key</li>
            </ul>
        </li>
        <li>
            <p><strong>Configure in Plugin</strong></p>
            <ul>
                <li>Navigate to <strong>SAM AI CC &gt; Settings &gt; AI Models</strong></li>
                <li>Paste API key in Gemini API Key field</li>
                <li>Save settings</li>
            </ul>
        </li>
    </ol> 
    <h4>OpenAI GPT</h4>
    <ol>
        <li>
            <p><strong>Get API Key</strong></p>
            <ul>
                <li>Go to <a href="https://platform.openai.com/api-keys">OpenAI Platform</a></li>
                <li>Sign in or create account</li>
                <li>Click <strong>Create new secret key</strong></li>
                <li>Copy the API key immediately (won't be shown again)</li>
            </ul>
        </li>
        <li>
            <p><strong>Configure in Plugin</strong></p>
            <ul>
                <li>Navigate to <strong>SAM AI CC &gt; Settings &gt; AI Models</strong></li>
                <li>Paste API key in OpenAI API Key field</li>
                <li>Save settings</li>
            </ul>
        </li>
    </ol>
    <h3>4. Optional: Automation Setup</h3>
    <h4>Enable Automated Reports</h4>
    <ol>
        <li>Navigate to <strong>SAM AI CC &gt; Settings &gt; Automation</strong></li>
        <li>Check <strong>Enable weekly reports</strong> and/or <strong>Enable monthly reports</strong></li>
        <li>Reports will be sent to the WordPress admin email</li>
    </ol>
    <h4>Slack Integration</h4>
    <ol>
        <li>
            <p><strong>Create Slack Webhook</strong></p>
            <ul>
                <li>Go to your <a href="https://api.slack.com/messaging/webhooks">Slack workspace settings</a></li>
                <li>Create an Incoming Webhook</li>
                <li>Choose the channel for notifications</li>
                <li>Copy the Webhook URL</li>
            </ul>
        </li>
        <li>
            <p><strong>Configure in Plugin</strong></p>
            <ul>
                <li>Navigate to <strong>SAM AI CC &gt; Settings &gt; Automation</strong></li>
                <li>Paste Webhook URL in Slack Webhook URL field</li>
                <li>Save settings</li>
            </ul>
        </li>
    </ol> -->
</div>
</div>

<style>
.sam-help {
    max-width: 1200px;
}

.card {
    margin-bottom: 20px;
    min-width: 100%;
}
</style>