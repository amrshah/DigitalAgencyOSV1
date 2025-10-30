sam-ai-command-center/
│
├── 📄 sam-ai-command-center.php          ← Main plugin file
├── 📄 README.md                          ← Setup documentation
│
├── 📁 includes/
│   ├── 📄 class-sam-ai-core.php                    ← Core logic
│   ├── 📄 class-sam-ai-wrapper-googleads.php       ← Google Ads integration
│   ├── 📄 class-sam-ai-wrapper-ga4.php             ← GA4 integration
│   ├── 📄 class-sam-ai-wrapper-wp.php              ← WordPress data
│   ├── 📄 class-sam-ai-adapter-openai.php          ← OpenAI GPT adapter
│   └── 📄 class-sam-ai-adapter-gemini.php          ← Gemini adapter
│
├── 📁 admin/
│   ├── 📄 admin-ui.php                   ← Main dashboard UI
│   ├── 📄 settings-ui.php                ← Settings page UI
│   │
│   ├── 📁 js/
│   │   └── 📄 admin.js                   ← JavaScript functionality
│   │
│   └── 📁 css/
│       └── 📄 admin.css                  ← Styling
│
└── 📁 logs/
    └── 📄 .htaccess                      ← Security (deny access)