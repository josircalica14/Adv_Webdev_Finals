# BSIT Student Portfolio

A modern, feature-rich portfolio website showcasing technical skills, projects, and experience. Built with PHP, JavaScript, and enhanced with Three.js 3D visualizations and AI-powered chatbot integration.

## ✨ Features

- **Dynamic Skills Section**: Interactive skill cards with proficiency indicators and smooth animations
- **Project Gallery**: Filterable project showcase with detailed modal views
- **Contact Form**: Functional contact form with email integration and validation
- **Resume Download**: One-click resume download with tracking
- **Three.js 3D Background**: Interactive particle field with mouse interaction
- **AI Chatbot**: Gemini-powered conversational assistant for visitor engagement
- **Fully Responsive**: Mobile-first design that works on all devices
- **Accessibility Compliant**: WCAG 2.1 AA standards with keyboard navigation and screen reader support
- **Cross-Browser Compatible**: Works on Chrome, Firefox, Safari, and Edge

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Modern web browser
- Google Gemini API key (free tier available)

### Installation

1. **Clone or download the repository**
   ```bash
   git clone <your-repo-url>
   cd portfolio
   ```

2. **Configure the portfolio**
   - Edit `js/config.js` to add your personal information
   - Update `data/skills-data.js` with your skills
   - Update `data/projects-data.js` with your projects
   - Add your resume PDF to `assets/resume.pdf`

3. **Get Gemini API Key** (for AI chatbot)
   - Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
   - Sign in with your Google account
   - Click "Create API Key"
   - Copy the API key
   - Add it to `js/config.js` in the `chatbot.geminiApiKey` field

4. **Configure email settings**
   - Edit `includes/config.php` with your email settings
   - Update `contact-handler.php` if using SMTP

5. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

6. **Open in browser**
   ```
   http://localhost:8000
   ```

## 📁 Project Structure

```
portfolio/
├── index.php                 # Homepage
├── about.php                 # About page
├── contact.php               # Contact page
├── resume-download.php       # Resume download handler
├── contact-handler.php       # Form submission backend
│
├── includes/
│   ├── header.php           # Site header with dependencies
│   ├── nav.php              # Navigation menu
│   ├── footer.php           # Site footer
│   └── config.php           # Configuration settings
│
├── css/
│   ├── style.css            # Main stylesheet
│   ├── skills.css           # Skills section styles
│   ├── projects.css         # Projects gallery styles
│   ├── chatbot.css          # Chatbot interface styles
│   ├── loading.css          # Loading states and animations
│   └── accessibility.css    # Accessibility enhancements
│
├── js/
│   ├── config.js            # Configuration file (CUSTOMIZE THIS)
│   ├── skills.js            # Skills section logic
│   ├── projects.js          # Projects gallery functionality
│   ├── chatbot.js           # AI chatbot integration
│   ├── three-background.js  # Three.js 3D visualization
│   ├── contact-form.js      # Form handling
│   ├── loading.js           # Loading states manager
│   ├── accessibility.js     # Accessibility enhancements
│   ├── polyfills.js         # Browser compatibility
│   └── app.js               # General utilities
│
├── data/
│   ├── skills-data.js       # Skills configuration (CUSTOMIZE THIS)
│   └── projects-data.js     # Projects configuration (CUSTOMIZE THIS)
│
├── assets/
│   └── resume.pdf           # Your resume (ADD YOUR FILE)
│
└── images/
    └── projects/            # Project thumbnails
```

## 🎨 Customization Guide

### 1. Personal Information

Edit `js/config.js`:

```javascript
personal: {
  name: "Your Name",
  title: "BSIT Student",
  email: "your.email@example.com",
  // ... other settings
}
```

### 2. Skills

Edit `data/skills-data.js`:

```javascript
const skillsData = {
  "Programming Languages": [
    { 
      name: "JavaScript", 
      level: 90, 
      icon: "fab fa-js",
      description: "Expert in ES6+ and modern frameworks"
    },
    // Add more skills...
  ],
  // Add more categories...
};
```

**Skill Properties:**
- `name`: Skill name (required)
- `level`: Proficiency 0-100 (required)
- `icon`: Font Awesome class (optional)
- `description`: Hover text (optional)

### 3. Projects

Edit `data/projects-data.js`:

```javascript
const projectsData = [
  {
    id: 1,
    title: "Project Name",
    description: "Short description",
    thumbnail: "images/projects/project1.jpg",
    technologies: ["React", "Node.js", "MongoDB"],
    category: "Web Development",
    liveUrl: "https://demo.example.com",
    githubUrl: "https://github.com/user/project",
    detailedDescription: "Full project description...",
    features: ["Feature 1", "Feature 2", "Feature 3"]
  },
  // Add more projects...
];
```

**Project Properties:**
- `id`: Unique identifier (required)
- `title`: Project name (required)
- `description`: Short description (required)
- `thumbnail`: Image path (required)
- `technologies`: Array of tech used (required)
- `category`: Filter category (required)
- `liveUrl`: Live demo link (optional)
- `githubUrl`: Repository link (optional)
- `detailedDescription`: Full description (optional)
- `features`: Key features list (optional)

### 4. AI Chatbot

Get your free Gemini API key:

1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with Google account
3. Create API key
4. Add to `js/config.js`:

```javascript
chatbot: {
  geminiApiKey: "YOUR_API_KEY_HERE",
  systemContext: `Customize this with your information...`
}
```

**Customize the chatbot context** with your:
- Skills and expertise
- Projects and achievements
- Education background
- Career interests

### 5. Contact Form

Edit `includes/config.php`:

```php
<?php
define('CONTACT_EMAIL', 'your.email@example.com');
define('SITE_NAME', 'Your Portfolio');
?>
```

For SMTP email (recommended for production):
- Install PHPMailer: `composer require phpmailer/phpmailer`
- Configure SMTP settings in `contact-handler.php`

### 6. Resume

1. Export your resume as PDF
2. Save as `assets/resume.pdf`
3. Update filename in `js/config.js` if different:

```javascript
resume: {
  filename: "resume.pdf",
  downloadName: "YourName_Resume.pdf"
}
```

### 7. Colors & Theme

Edit `js/config.js`:

```javascript
theme: {
  colors: {
    background: "#eaeaea",
    accent: "#d6a5ad",
    text: "#0f0f0f"
  }
}
```

Or edit CSS variables in `css/style.css`:

```css
:root {
  --bg-color: #eaeaea;
  --accent-color: #d6a5ad;
  --text-color: #0f0f0f;
}
```

### 8. Three.js Settings

Edit `js/config.js`:

```javascript
threeJS: {
  particleCount: {
    desktop: 300,
    mobile: 150
  },
  particleColor: 0xd6a5ad,
  enableOnMobile: true
}
```

## 🔧 Configuration Files

### Main Configuration: `js/config.js`
Central configuration for all customizable settings. Edit this file first!

### Skills Data: `data/skills-data.js`
Define your technical skills organized by category.

### Projects Data: `data/projects-data.js`
Showcase your projects with images, descriptions, and links.

### Email Config: `includes/config.php`
Email settings for contact form.

## 📧 Email Setup

### Option 1: PHP mail() Function (Simple)

Works on most shared hosting. Already configured in `contact-handler.php`.

### Option 2: SMTP (Recommended)

For better deliverability:

1. Install PHPMailer:
   ```bash
   composer require phpmailer/phpmailer
   ```

2. Edit `contact-handler.php`:
   ```php
   use PHPMailer\PHPMailer\PHPMailer;
   
   $mail = new PHPMailer(true);
   $mail->isSMTP();
   $mail->Host = 'smtp.gmail.com';
   $mail->SMTPAuth = true;
   $mail->Username = 'your.email@gmail.com';
   $mail->Password = 'your-app-password';
   $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
   $mail->Port = 587;
   ```

3. For Gmail, create an [App Password](https://support.google.com/accounts/answer/185833)

## 🎯 Adding Project Images

1. Create images at 800x600px (or 4:3 ratio)
2. Optimize images (use tools like TinyPNG)
3. Save to `images/projects/`
4. Reference in `data/projects-data.js`:
   ```javascript
   thumbnail: "images/projects/my-project.jpg"
   ```

## 🚀 Deployment

### Production Deployment

For production deployment of the multi-user portfolio platform, see:
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Complete deployment guide
- **[DEPLOYMENT-QUICK-START.md](DEPLOYMENT-QUICK-START.md)** - Quick reference guide
- **[SECURITY-BEST-PRACTICES.md](SECURITY-BEST-PRACTICES.md)** - Security guidelines

Key production requirements:
- PHP 7.4+ with required extensions
- MySQL 8.0+
- SSL certificate (HTTPS required)
- SMTP credentials for email
- Proper file permissions and security configuration

### Deploy to Shared Hosting

1. Upload all files via FTP
2. Ensure PHP 7.4+ is available
3. Set file permissions (755 for directories, 644 for files)
4. Update `js/config.js` with production settings
5. Test contact form and resume download

### Deploy to Heroku

1. Create `composer.json`:
   ```json
   {
     "require": {
       "php": "^7.4.0"
     }
   }
   ```

2. Create `Procfile`:
   ```
   web: vendor/bin/heroku-php-apache2
   ```

3. Deploy:
   ```bash
   git init
   heroku create
   git add .
   git commit -m "Initial commit"
   git push heroku main
   ```

### Deploy to Netlify (Static Version)

Convert PHP to static HTML:
1. Save each PHP page as HTML
2. Use Netlify Forms for contact form
3. Deploy via Netlify CLI or GitHub integration

## 🧪 Testing

### Local Testing

```bash
# Start PHP server
php -S localhost:8000

# Open in browser
open http://localhost:8000
```

### Cross-Browser Testing

Test on:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

See `CROSS-BROWSER-TESTING.md` for detailed checklist.

### Mobile Testing

1. Use browser DevTools device emulation
2. Test on real devices (iOS and Android)
3. Check touch interactions
4. Verify responsive breakpoints

### Accessibility Testing

1. Keyboard navigation (Tab, Enter, Escape)
2. Screen reader testing (NVDA, JAWS, VoiceOver)
3. Color contrast (use browser extensions)
4. WCAG compliance check

## 🐛 Troubleshooting

### Chatbot Not Working

**Problem**: Chatbot doesn't respond

**Solutions**:
1. Check API key in `js/config.js`
2. Verify API key is active in Google AI Studio
3. Check browser console for errors
4. Ensure internet connection is active
5. Check API rate limits (60 requests/minute on free tier)

### Contact Form Not Sending

**Problem**: Form submits but email not received

**Solutions**:
1. Check spam folder
2. Verify email in `includes/config.php`
3. Check server supports `mail()` function
4. Try SMTP configuration instead
5. Check server error logs

### Three.js Not Loading

**Problem**: 3D background not showing

**Solutions**:
1. Check browser supports WebGL
2. Open browser console for errors
3. Verify Three.js CDN is accessible
4. Check `js/three-background.js` is loaded
5. Try disabling browser extensions

### Resume Download Not Working

**Problem**: Resume download fails

**Solutions**:
1. Verify `assets/resume.pdf` exists
2. Check file permissions (644)
3. Verify filename in `js/config.js` matches actual file
4. Check browser console for errors
5. Try direct link: `http://localhost:8000/assets/resume.pdf`

### Skills/Projects Not Displaying

**Problem**: Sections are empty

**Solutions**:
1. Check `data/skills-data.js` and `data/projects-data.js` exist
2. Verify JavaScript syntax (no errors in console)
3. Check files are loaded in `includes/header.php`
4. Verify data structure matches expected format

### Mobile Layout Issues

**Problem**: Layout broken on mobile

**Solutions**:
1. Check viewport meta tag in header
2. Test responsive breakpoints in DevTools
3. Verify CSS media queries
4. Check for horizontal scrolling
5. Test on real device, not just emulator

## 📚 Additional Resources

### Documentation
- [Three.js Documentation](https://threejs.org/docs/)
- [Google Gemini API](https://ai.google.dev/docs)
- [Font Awesome Icons](https://fontawesome.com/icons)
- [WCAG Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

### Tools
- [TinyPNG](https://tinypng.com/) - Image compression
- [Can I Use](https://caniuse.com/) - Browser compatibility
- [WAVE](https://wave.webaim.org/) - Accessibility checker
- [Lighthouse](https://developers.google.com/web/tools/lighthouse) - Performance audit

### Learning Resources
- [MDN Web Docs](https://developer.mozilla.org/)
- [PHP Manual](https://www.php.net/manual/en/)
- [JavaScript.info](https://javascript.info/)

## 🤝 Contributing

This is a personal portfolio template. Feel free to:
- Fork and customize for your own use
- Report bugs or issues
- Suggest improvements
- Share your customized version

## 📄 License

This project is open source and available for personal and commercial use.

## 💬 Support

If you need help:
1. Check this README thoroughly
2. Review `CROSS-BROWSER-TESTING.md` for testing issues
3. Check browser console for error messages
4. Search for similar issues online
5. Contact the developer

## 🎓 Credits

Built with:
- PHP for backend
- JavaScript (ES6+) for interactivity
- Three.js for 3D graphics
- Google Gemini API for AI chatbot
- Font Awesome for icons
- Bootstrap for responsive grid

## 📝 Changelog

### Version 1.0.0 (Current)
- Initial release
- Dynamic skills section
- Project gallery with filtering
- Contact form with email integration
- Resume download functionality
- Three.js 3D background
- AI chatbot integration
- Full responsive design
- Accessibility compliance
- Cross-browser compatibility

---

**Made with ❤️ by [Your Name]**

Last Updated: [Current Date]
