# TechText - Markup Language Converter

A beautiful, modern Progressive Web App (PWA) that converts various markup languages to different output formats. Built with PHP, vanilla JavaScript (via CDN), and SQLite.

**Built by:** Santosh Baral  
**Company:** Techzen Corporation  
**Website:** https://techzeninc.com

![TechText Screenshot](screenshots/desktop.png)

## ✨ Features

### Core Features
- **Multiple Markup Support:** Convert Markdown, BBCode, reStructuredText, Textile, Wiki Markup, and HTML
- **Flexible Output:** Generate Plain Text, Rich Text (HTML), Clean HTML, or JSON
- **File Upload:** Drag & drop or browse files (max 2MB) with auto-detection
- **Conversion History:** Automatic storage with CRUD operations
- **Security:** CSRF protection, XSS prevention, SQL injection protection

### PWA Features
- **Installable:** Add to home screen on mobile and desktop
- **Offline Detection:** Graceful handling when offline
- **Fast Loading:** Cached assets for instant load times
- **Background Sync:** Queue conversions when back online
- **Responsive:** Works seamlessly on all devices
- **App-like Experience:** Full-screen mode, splash screen, standalone window

### UI/UX Features
- **Modern Design:** Glassmorphism UI with gradient backgrounds
- **Smooth Animations:** Fade-in, slide-up effects throughout
- **Keyboard Shortcuts:** 
  - `Ctrl/Cmd + Enter` - Convert
  - `Ctrl/Cmd + S` - Download
  - `Ctrl/Cmd + C` - Copy output
- **Real-time Character Count:** With visual warnings
- **Toast Notifications:** Success/error feedback
- **Syntax Highlighting:** Code blocks highlighted with Prism.js
- **Mobile-Optimized:** Floating action button for quick convert

## 🚀 Requirements

- PHP 7.4 or higher
- SQLite3 extension enabled
- Web server (Apache/Nginx)
- 50MB disk space
- HTTPS (required for PWA features)

## 📦 Installation

### Quick Start

1. **Download and extract** to your web server:
   ```bash
   /var/www/html/techtext/
   ```

2. **Set permissions**:
   ```bash
   chmod 755 data/
   chown www-data:www-data data/
   ```

3. **Check requirements**:
   ```
   http://yourdomain.com/techtext/check.php
   ```

4. **Launch application**:
   ```
   http://yourdomain.com/techtext/
   ```

### Enable HTTPS (Required for PWA)

```bash
# Using Let's Encrypt (Certbot)
sudo certbot --apache -d yourdomain.com

# Or manually configure SSL in your web server
```

## 📁 File Structure

```
techtext/
├── index.php          # Main application interface
├── api.php            # API endpoints
├── config.php         # Configuration
├── database.php       # SQLite database layer
├── parsers.php        # Markup parsers
├── app.js             # Client-side JavaScript
├── sw.js              # Service Worker (PWA)
├── manifest.json      # PWA manifest
├── offline.html       # Offline page
├── check.php          # Server requirements check
├── .htaccess          # Apache configuration
├── README.md          # Documentation
├── icons/             # PWA icons
│   ├── icon.svg
│   └── generate-icons.js
├── screenshots/       # PWA screenshots
└── data/              # Database storage
    └── techtext.db
```

## 🎯 Supported Formats

### Input Formats
| Format | Description |
|--------|-------------|
| Markdown | Standard Markdown with tables, code blocks |
| BBCode | Bulletin Board Code with common tags |
| reStructuredText | RST with directives and roles |
| Textile | Textile markup language |
| Wiki Markup | MediaWiki-style syntax |
| HTML | Passthrough with cleaning |

### Output Formats
| Format | Description |
|--------|-------------|
| Plain Text | Stripped of all markup |
| Rich Text | Full HTML with styling |
| Clean HTML | Sanitized HTML |
| JSON | Structured output |

## 📱 PWA Installation

### Android (Chrome)
1. Visit the site in Chrome
2. Tap the menu (⋮) → "Add to Home screen"
3. Tap "Install"

### iOS (Safari)
1. Visit the site in Safari
2. Tap Share button → "Add to Home Screen"
3. Tap "Add"

### Desktop (Chrome/Edge)
1. Visit the site
2. Click install icon (➕) in address bar
3. Click "Install"

## 🔒 Security Features

- **CSRF Protection:** All POST requests require valid tokens
- **XSS Prevention:** Output escaped and sanitized
- **SQL Injection:** Prepared statements throughout
- **File Upload Validation:** MIME type and size checks
- **Secure Headers:** X-Frame-Options, CSP, etc.
- **Session Security:** HttpOnly cookies, strict mode

## 🎨 Customization

### Change Theme Colors
Edit `manifest.json`:
```json
{
  "theme_color": "#2563eb",
  "background_color": "#ffffff"
}
```

### Update Icons
1. Edit `icons/icon.svg`
2. Convert to PNG sizes: 72x72, 96x96, 128x128, 144x144, 152x152, 192x192, 384x384, 512x512
3. Place in `icons/` folder

### Modify Styles
Edit inline styles in `index.php` or add custom CSS.

## 🔧 API Usage

```bash
# Convert markup
curl -X POST https://yourdomain.com/techtext/api.php?action=convert \
  -H "X-CSRF-Token: YOUR_TOKEN" \
  -d "content=**Bold Text**" \
  -d "markup_type=markdown" \
  -d "output_format=html"

# Get history
curl https://yourdomain.com/techtext/api.php?action=history

# Upload file
curl -X POST https://yourdomain.com/techtext/api.php?action=upload \
  -H "X-CSRF-Token: YOUR_TOKEN" \
  -F "file=@document.md"
```

## 🐛 Troubleshooting

### PWA Not Installing
- Ensure HTTPS is enabled
- Check manifest.json is accessible
- Verify service worker is registered

### Database Permission Errors
```bash
chmod 777 data/
touch data/techtext.db
chmod 666 data/techtext.db
```

### Service Worker Not Updating
1. Open DevTools → Application → Service Workers
2. Click "Update" or "Unregister"
3. Hard refresh (Ctrl+F5)

### Cache Issues
```javascript
// In browser console:
navigator.serviceWorker.getRegistrations().then(regs => {
  regs.forEach(reg => reg.unregister());
});
```

## 📄 License

Copyright © 2026 Techzen Corporation. All rights reserved.

## 🆘 Support

For support, please contact:
- **Website:** https://techzeninc.com
- **Built by:** Santosh Baral

---

**TechText** - Making markup conversion beautiful and accessible anywhere.