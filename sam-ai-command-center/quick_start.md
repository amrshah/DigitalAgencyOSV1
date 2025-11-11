# SAM AI Command Center - Quick Start Guide

## 5-Minute Setup

### Step 1: Get the Files (2 minutes)

1. **Copy each artifact** from Claude's conversation above
2. **Save each file** with its exact name in the correct folder
3. **Follow this exact structure**:

```
sam-ai-command-center/          ← Create this folder first
├── sam-ai-command-center.php   ← Copy from artifact 1
├── README.md                    ← Copy from artifact 12
├── includes/                    ← Create this folder
│   ├── class-sam-ai-core.php              ← Artifact 2
│   ├── class-sam-ai-wrapper-googleads.php ← Artifact 3
│   ├── class-sam-ai-wrapper-ga4.php       ← Artifact 4
│   ├── class-sam-ai-wrapper-wp.php        ← Artifact 5
│   ├── class-sam-ai-adapter-gemini.php    ← Artifact 6
│   └── class-sam-ai-adapter-openai.php    ← Artifact 7
├── admin/                       ← Create this folder
│   ├── admin-ui.php            ← Artifact 8
│   ├── settings-ui.php         ← Artifact 9
│   ├── js/                     ← Create this folder
│   │   └── admin.js            ← Artifact 10
│   └── css/                    ← Create this folder
│       └── admin.css           ← Artifact 11
└── logs/                        ← Create this folder
    └── .htaccess                ← Type: Deny from all
```

### Step 2: Create ZIP File (1 minute)

**Mac/Linux:**
```bash
zip -r sam-ai-command-center.zip sam-ai-command-center/
```

**Windows (PowerShell):**
```powershell
Compress-Archive -Path sam-ai-command-center -DestinationPath sam-ai-command-center.zip
```

**Windows (GUI):**
- Right-click folder → "Send to" → "Compressed folder"

### Step 3: Install in WordPress (1 minute)

1. Go to **Plugins → Add New → Upload Plugin**
2. Choose `sam-ai-command-center.zip`
3. Click **Install Now**
4. Click **Activate Plugin**

### Step 4: Basic Configuration (1 minute)

1. Go to **SAM AI CC → Settings**
2. **Minimum Setup** (choose ONE):
   - **Option A**: Add Gemini API key only (easiest, free tier)
   - **Option B**: Add OpenAI API key only (more powerful)
3. Click **Save Settings**
4. Go to **SAM AI CC** (main page)
5. Try a query: "Show me mock data for testing"

---

## Minimal Working Setup

You can start using the plugin with **just one API key**:

### Option 1: Gemini Only (FREE)
1. Get API key from [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Enter in **Settings → AI Models → Gemini API Key**
3. Plugin will use mock data for Google Ads and GA4
4. Still get AI-powered insights!

### Option 2: OpenAI Only
1. Get API key from [OpenAI Platform](https://platform.openai.com/api-keys)
2. Enter in **Settings → AI Models → OpenAI API Key**
3. Plugin will use mock data for Google Ads and GA4
4. Get GPT-powered analysis!

---

## Full Setup (When Ready)

Add these for real data:

### Google Ads (Optional)
- Client ID, Client Secret, Refresh Token, Developer Token
- **Guide**: README.md → Google Ads API Setup

### Google Analytics 4 (Optional)
- Property ID
- Service Account JSON
- **Guide**: README.md → Google Analytics 4 Setup

---

## Verify Installation

### Check 1: Menu Item
- Look for **SAM AI CC** in WordPress admin sidebar
- Should have two items: Dashboard and Settings

### Check 2: Database Table
```sql
-- Run in phpMyAdmin or database tool
SHOW TABLES LIKE '%sam_ai_cache%';
-- Should return: wp_sam_ai_cache
```

### Check 3: Logs Directory
- Check: `wp-content/plugins/sam-ai-command-center/logs/`
- Should exist with `.htaccess` file

### Check 4: Test Query
1. Go to **SAM AI CC**
2. Enter: "Summarize my marketing performance"
3. Click **Generate Insights**
4. Should see results (mock data if APIs not configured)

---

## Troubleshooting

### "Plugin could not be activated"
- **Fix**: Check PHP version (must be 8.0+)
- Go to **Tools → Site Health → Info → Server**

### "Headers already sent"
- **Fix**: Check for extra spaces/newlines at start of PHP files
- Each PHP file should start exactly with `<?php` (no space before)

### "Class not found"
- **Fix**: Verify all files in `includes/` folder
- Check file names are exact (case-sensitive)

### "Permission denied"
- **Fix**: Set folder permissions:
  - Folders: `chmod 755`
  - Files: `chmod 644`

### No menu item appears
- **Fix**: 
  1. Deactivate plugin
  2. Reactivate plugin
  3. Clear browser cache
  4. Try different user (must be Administrator)

---

## First Query Examples

Try these with mock data (no API keys needed):

1. **"What were my top campaigns this month?"**
   - Tests basic query functionality

2. **"Show me traffic sources and conversion data"**
   - Tests data formatting

3. **"Compare this week with last week"**
   - Tests date range handling

4. **"Give me 3 actionable recommendations"**
   - Tests AI reasoning

---

## 📊 Expected Behavior

### With Mock Data (No API Keys)
- ✅ Plugin works perfectly
- ✅ AI generates insights
- ⚠️ Data is sample/demo data
- ⚠️ Note: "Mock data" shown in response

### With Real APIs Configured
- ✅ Plugin works perfectly
- ✅ AI generates insights
- ✅ Real campaign data
- ✅ Real analytics data
- ✅ Real WordPress stats

---

## Learning Path

### Week 1: Test with Mock Data
- Get familiar with the interface
- Try different queries
- Understand the output format
- No API costs!

### Week 2: Add One API
- Start with Gemini (free tier)
- Configure Google Ads OR GA4
- Compare mock vs real data

### Week 3: Full Setup
- Add remaining APIs
- Enable automated reports
- Set up Slack notifications
- Optimize for cost

---

## Need Help?

1. **Check README.md** - Comprehensive guide
2. **Check Logs** - `logs/queries.log`
3. **WordPress Debug** - Enable in `wp-config.php`
4. **Site Health** - Tools → Site Health

---

## Success Checklist

- [ ] Plugin activated without errors
- [ ] "SAM AI CC" menu appears
- [ ] Settings page loads
- [ ] Can save settings
- [ ] Dashboard loads
- [ ] Can submit a query
- [ ] Results appear (even if mock data)
- [ ] No PHP errors in debug log

**If all checked, you're ready to go! 🚀**

---

## Time Estimates

| Task | Time | Difficulty |
|------|------|------------|
| Create file structure | 5 min | Easy |
| Copy all files | 10 min | Easy |
| Create ZIP | 1 min | Easy |
| Install plugin | 2 min | Easy |
| Get Gemini API key | 2 min | Easy |
| Basic config | 3 min | Easy |
| First query | 1 min | Easy |
| **TOTAL** | **24 min** | ⭐⭐☆☆☆ |

**Full setup with all APIs: 1-2 hours** (API account creation takes time)

---

** Bottom Line**: You can have the plugin running with AI-powered insights in under 30 minutes, even without any marketing APIs configured!
