# Troubleshooting Guide

Common issues and solutions for the BSIT Student Portfolio.

## Table of Contents

1. [AI Chatbot Issues](#ai-chatbot-issues)
2. [Contact Form Issues](#contact-form-issues)
3. [Three.js / 3D Background Issues](#threejs--3d-background-issues)
4. [Resume Download Issues](#resume-download-issues)
5. [Skills/Projects Not Displaying](#skillsprojects-not-displaying)
6. [Mobile/Responsive Issues](#mobileresponsive-issues)
7. [Performance Issues](#performance-issues)
8. [Browser Compatibility Issues](#browser-compatibility-issues)
9. [Email Configuration Issues](#email-configuration-issues)
10. [General Debugging Tips](#general-debugging-tips)

---

## AI Chatbot Issues

### Issue: Chatbot doesn't open when clicked

**Symptoms:**
- Clicking chatbot button does nothing
- No console errors

**Solutions:**

1. **Check JavaScript is loaded:**
   ```javascript
   // Open browser console and type:
   console.log(typeof initChatbot);
   // Should return "function", not "undefined"
   ```

2. **Verify chatbot.js is included:**
   - Check `includes/header.php` includes `<script src="js/chatbot.js"></script>`
   - Check file exists at `js/chatbot.js`

3. **Check for JavaScript errors:**
   - Open browser DevTools (F12)
   - Go to Console tab
   - Look for red error messages
   - Fix any syntax errors

### Issue: Chatbot opens but doesn't respond

**Symptoms:**
- Chatbot window opens
- Messages sent but no response
- May show "Failed to get response" error

**Solutions:**

1. **Verify API key is set:**
   ```javascript
   // Check js/config.js
   chatbot: {
     geminiApiKey: "YOUR_ACTUAL_API_KEY_HERE" // Not placeholder text
   }
   ```

2. **Test API key validity:**
   - Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
   - Verify key is active and not expired
   - Check usage limits haven't been exceeded

3. **Check network requests:**
   - Open DevTools → Network tab
   - Send a message in chatbot
   - Look for request to `generativelanguage.googleapis.com`
   - Check response status (should be 200)
   - If 401: API key invalid
   - If 429: Rate limit exceeded
   - If 403: API key doesn't have permission

4. **Check CORS issues:**
   - If testing locally, ensure you're using `http://localhost` not `file://`
   - API should work from any domain

5. **Verify internet connection:**
   - Chatbot requires active internet to reach Gemini API
   - Test: `ping generativelanguage.googleapis.com`

### Issue: Chatbot shows "undefined" or blank responses

**Symptoms:**
- Chatbot responds but message is empty or "undefined"

**Solutions:**

1. **Check API response structure:**
   ```javascript
   // In chatbot.js, add console.log to see response:
   console.log('API Response:', data);
   ```

2. **Verify response parsing:**
   - Gemini API structure: `data.candidates[0].content.parts[0].text`
   - Check if structure changed in API update

3. **Update chatbot.js if needed:**
   ```javascript
   // Ensure correct path to response text
   const aiResponse = data.candidates?.[0]?.content?.parts?.[0]?.text || 
                      "Sorry, I couldn't generate a response.";
   ```

### Issue: Rate limit exceeded

**Symptoms:**
- Error: "429 Too Many Requests"
- Chatbot stops working after several messages

**Solutions:**

1. **Free tier limits:**
   - 60 requests per minute
   - Wait 1 minute and try again

2. **Implement rate limiting:**
   ```javascript
   // Add to chatbot.js
   let lastRequestTime = 0;
   const MIN_REQUEST_INTERVAL = 1000; // 1 second

   function canSendMessage() {
     const now = Date.now();
     if (now - lastRequestTime < MIN_REQUEST_INTERVAL) {
       return false;
     }
     lastRequestTime = now;
     return true;
   }
   ```

3. **Upgrade API plan:**
   - Consider paid tier for higher limits
   - Visit Google AI Studio for pricing

---

## Contact Form Issues

### Issue: Form submits but email not received

**Symptoms:**
- Success message shows
- No email arrives (check spam too)

**Solutions:**

1. **Verify email configuration:**
   ```php
   // Check includes/config.php
   define('CONTACT_EMAIL', 'your.actual.email@example.com');
   ```

2. **Check PHP mail() function:**
   ```bash
   # Test if server supports mail()
   php -r "mail('test@example.com', 'Test', 'Test message');"
   ```

3. **Check server mail logs:**
   ```bash
   # On Linux servers
   tail -f /var/log/mail.log
   ```

4. **Try SMTP instead:**
   - Install PHPMailer: `composer require phpmailer/phpmailer`
   - Configure SMTP in `contact-handler.php`
   - Use Gmail, SendGrid, or Mailgun

5. **Check spam folder:**
   - Emails from PHP mail() often go to spam
   - Add sender to contacts
   - Use SMTP for better deliverability

### Issue: Form shows validation errors incorrectly

**Symptoms:**
- Valid email marked as invalid
- Required fields not detected

**Solutions:**

1. **Check JavaScript validation:**
   ```javascript
   // In js/contact-form.js
   // Verify email regex pattern
   const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
   ```

2. **Check PHP validation:**
   ```php
   // In contact-handler.php
   $email = filter_var($_POST['contact-email'], FILTER_VALIDATE_EMAIL);
   if (!$email) {
     // Email invalid
   }
   ```

3. **Check form field names:**
   - Ensure form input names match handler expectations
   - `contact-name`, `contact-email`, `contact-project`

### Issue: Form submission fails with error

**Symptoms:**
- Error message displayed
- Console shows 500 or 404 error

**Solutions:**

1. **Check handler file exists:**
   - Verify `contact-handler.php` is in root directory
   - Check file permissions (644)

2. **Check PHP errors:**
   ```php
   // Add to top of contact-handler.php for debugging
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Check AJAX request:**
   ```javascript
   // In browser console, check Network tab
   // Look for POST request to contact-handler.php
   // Check request payload and response
   ```

4. **Verify CORS if on different domain:**
   ```php
   // Add to contact-handler.php if needed
   header('Access-Control-Allow-Origin: *');
   ```

---

## Three.js / 3D Background Issues

### Issue: 3D background not showing

**Symptoms:**
- Blank space where 3D background should be
- No particles visible

**Solutions:**

1. **Check WebGL support:**
   ```javascript
   // In browser console:
   console.log(window.detectWebGL());
   // Should return true
   ```

2. **Check Three.js is loaded:**
   ```javascript
   // In browser console:
   console.log(typeof THREE);
   // Should return "object", not "undefined"
   ```

3. **Verify CDN is accessible:**
   - Check `includes/header.php` has Three.js CDN link
   - Test CDN URL in browser: `https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js`

4. **Check canvas element:**
   ```javascript
   // In browser console:
   console.log(document.getElementById('three-canvas'));
   // Should return canvas element, not null
   ```

5. **Check for JavaScript errors:**
   - Open DevTools Console
   - Look for errors in `three-background.js`
   - Common: "THREE is not defined" = CDN not loaded

### Issue: 3D background is laggy or slow

**Symptoms:**
- Low frame rate
- Stuttering animation
- Browser becomes unresponsive

**Solutions:**

1. **Reduce particle count:**
   ```javascript
   // In js/config.js
   threeJS: {
     particleCount: {
       desktop: 150, // Reduce from 300
       mobile: 75    // Reduce from 150
     }
   }
   ```

2. **Check device performance:**
   - Older devices may struggle with WebGL
   - Consider disabling on mobile:
   ```javascript
   threeJS: {
     enableOnMobile: false
   }
   ```

3. **Monitor FPS:**
   ```javascript
   // Add to three-background.js
   let lastTime = Date.now();
   function checkFPS() {
     const now = Date.now();
     const fps = 1000 / (now - lastTime);
     console.log('FPS:', fps);
     lastTime = now;
   }
   ```

4. **Disable on low-end devices:**
   ```javascript
   // Add to three-background.js
   if (navigator.hardwareConcurrency < 4) {
     // Disable or reduce particles
   }
   ```

### Issue: 3D background not responding to mouse

**Symptoms:**
- Particles visible but don't react to mouse movement

**Solutions:**

1. **Check mouse event listeners:**
   ```javascript
   // Verify in three-background.js
   document.addEventListener('mousemove', onMouseMove);
   ```

2. **Check canvas z-index:**
   ```css
   /* Canvas should be behind content */
   #three-canvas {
     position: fixed;
     z-index: -1;
   }
   ```

3. **Verify mouse coordinates:**
   ```javascript
   // Add to onMouseMove function
   console.log('Mouse:', mouseX, mouseY);
   ```

---

## Resume Download Issues

### Issue: Resume download button doesn't work

**Symptoms:**
- Clicking download button does nothing
- 404 error in console

**Solutions:**

1. **Verify resume file exists:**
   ```bash
   # Check file exists
   ls -la assets/resume.pdf
   ```

2. **Check file permissions:**
   ```bash
   # Should be readable (644)
   chmod 644 assets/resume.pdf
   ```

3. **Verify download handler:**
   - Check `resume-download.php` exists
   - Check file permissions (644)

4. **Test direct access:**
   - Try: `http://localhost:8000/assets/resume.pdf`
   - Should download or display PDF

5. **Check link in navigation:**
   ```php
   // In includes/nav.php
   <a href="resume-download.php">RESUME</a>
   // Or direct link:
   <a href="assets/resume.pdf" download>RESUME</a>
   ```

### Issue: Resume downloads with wrong filename

**Symptoms:**
- File downloads as "resume.pdf" instead of custom name

**Solutions:**

1. **Check download handler:**
   ```php
   // In resume-download.php
   header('Content-Disposition: attachment; filename="YourName_Resume.pdf"');
   ```

2. **Update config:**
   ```javascript
   // In js/config.js
   resume: {
     downloadName: "YourName_Resume.pdf"
   }
   ```

### Issue: Resume file too large

**Symptoms:**
- Slow download
- Server timeout

**Solutions:**

1. **Compress PDF:**
   - Use online tools: Smallpdf, iLovePDF
   - Target: Under 2MB

2. **Check file size:**
   ```bash
   ls -lh assets/resume.pdf
   ```

3. **Update max size in config:**
   ```javascript
   resume: {
     maxSizeMB: 5 // Adjust as needed
   }
   ```

---

## Skills/Projects Not Displaying

### Issue: Skills section is empty

**Symptoms:**
- Skills section shows but no skill cards
- Blank space where skills should be

**Solutions:**

1. **Check data file exists:**
   ```bash
   ls -la data/skills-data.js
   ```

2. **Verify data structure:**
   ```javascript
   // In data/skills-data.js
   const skillsData = {
     "Category Name": [
       { name: "Skill", level: 90, icon: "fab fa-js" }
     ]
   };
   ```

3. **Check for JavaScript errors:**
   - Open Console
   - Look for syntax errors in skills-data.js
   - Common: Missing comma, bracket, or quote

4. **Verify skills.js is loaded:**
   ```javascript
   // In browser console:
   console.log(typeof renderSkills);
   // Should return "function"
   ```

5. **Check container element:**
   ```javascript
   // In browser console:
   console.log(document.getElementById('skills-container'));
   // Should return element, not null
   ```

### Issue: Projects section is empty

**Symptoms:**
- Projects section shows but no project cards

**Solutions:**

1. **Check data file:**
   ```bash
   ls -la data/projects-data.js
   ```

2. **Verify data structure:**
   ```javascript
   // In data/projects-data.js
   const projectsData = [
     {
       id: 1,
       title: "Project",
       description: "Description",
       thumbnail: "images/projects/project1.jpg",
       technologies: ["Tech1"],
       category: "Category"
     }
   ];
   ```

3. **Check image paths:**
   - Verify images exist at specified paths
   - Check image file permissions (644)

4. **Check for JavaScript errors:**
   - Console errors in projects-data.js
   - Syntax errors in projects.js

### Issue: Project images not loading

**Symptoms:**
- Project cards show but images are broken

**Solutions:**

1. **Verify image files exist:**
   ```bash
   ls -la images/projects/
   ```

2. **Check image paths in data:**
   ```javascript
   // Should be relative to root
   thumbnail: "images/projects/project1.jpg"
   // Not: "/images/projects/project1.jpg"
   // Not: "../images/projects/project1.jpg"
   ```

3. **Check image permissions:**
   ```bash
   chmod 644 images/projects/*.jpg
   ```

4. **Test direct access:**
   - Try: `http://localhost:8000/images/projects/project1.jpg`
   - Should display image

5. **Check image format:**
   - Supported: JPG, PNG, WebP, GIF
   - Avoid: HEIC, TIFF, BMP

---

## Mobile/Responsive Issues

### Issue: Layout broken on mobile

**Symptoms:**
- Horizontal scrolling
- Elements overlapping
- Text too small or too large

**Solutions:**

1. **Check viewport meta tag:**
   ```html
   <!-- In includes/header.php -->
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   ```

2. **Test responsive breakpoints:**
   - Open DevTools (F12)
   - Toggle device toolbar (Ctrl+Shift+M)
   - Test at 320px, 768px, 1024px widths

3. **Check CSS media queries:**
   ```css
   /* Should have mobile-first approach */
   @media (max-width: 768px) {
     /* Mobile styles */
   }
   ```

4. **Check for fixed widths:**
   ```css
   /* Avoid: */
   .element { width: 1200px; }
   
   /* Use: */
   .element { max-width: 1200px; width: 100%; }
   ```

### Issue: Touch interactions not working

**Symptoms:**
- Buttons hard to tap
- Swipe gestures don't work

**Solutions:**

1. **Check touch target sizes:**
   ```css
   /* Minimum 44x44px for touch targets */
   button, a {
     min-width: 44px;
     min-height: 44px;
   }
   ```

2. **Add touch event listeners:**
   ```javascript
   // In addition to mouse events
   element.addEventListener('touchstart', handleTouch);
   ```

3. **Test on real device:**
   - Emulators don't always match real behavior
   - Test on actual iOS and Android devices

### Issue: Three.js too slow on mobile

**Symptoms:**
- Laggy animation on mobile
- Battery drain

**Solutions:**

1. **Reduce mobile particle count:**
   ```javascript
   // In js/config.js
   threeJS: {
     particleCount: {
       mobile: 50 // Reduce significantly
     }
   }
   ```

2. **Disable on mobile:**
   ```javascript
   threeJS: {
     enableOnMobile: false
   }
   ```

3. **Detect mobile and adjust:**
   ```javascript
   const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
   if (isMobile) {
     // Use fewer particles or disable
   }
   ```

---

## Performance Issues

### Issue: Slow page load

**Symptoms:**
- Page takes >5 seconds to load
- Lighthouse score below 70

**Solutions:**

1. **Optimize images:**
   ```bash
   # Use image compression tools
   # Target: <200KB per image
   ```

2. **Enable lazy loading:**
   ```html
   <img src="image.jpg" loading="lazy" alt="Description">
   ```

3. **Minify CSS/JS:**
   ```bash
   # Use minification tools
   npm install -g minify
   minify css/style.css > css/style.min.css
   ```

4. **Use CDN for libraries:**
   - Three.js, jQuery, Bootstrap from CDN
   - Faster than self-hosting

5. **Enable caching:**
   ```apache
   # In .htaccess
   <IfModule mod_expires.c>
     ExpiresActive On
     ExpiresByType image/jpg "access plus 1 year"
     ExpiresByType text/css "access plus 1 month"
     ExpiresByType application/javascript "access plus 1 month"
   </IfModule>
   ```

### Issue: High memory usage

**Symptoms:**
- Browser tab uses excessive RAM
- Browser becomes slow

**Solutions:**

1. **Reduce Three.js particles:**
   - Lower particle count
   - Simplify geometry

2. **Clean up event listeners:**
   ```javascript
   // Remove listeners when not needed
   element.removeEventListener('click', handler);
   ```

3. **Limit chat history:**
   ```javascript
   // In chatbot.js
   if (chatHistory.length > 20) {
     chatHistory = chatHistory.slice(-10); // Keep last 10
   }
   ```

---

## Browser Compatibility Issues

### Issue: Features not working in Safari

**Symptoms:**
- Works in Chrome but not Safari

**Solutions:**

1. **Check for webkit prefixes:**
   ```css
   .element {
     -webkit-transform: translateX(0);
     transform: translateX(0);
   }
   ```

2. **Test fetch API:**
   ```javascript
   // Safari may need polyfill
   if (!window.fetch) {
     // Load polyfill
   }
   ```

3. **Check date/time inputs:**
   - Safari handles differently
   - Use text inputs with validation

### Issue: Not working in Internet Explorer

**Symptoms:**
- Blank page or errors in IE11

**Solutions:**

1. **Load polyfills:**
   - Check `js/polyfills.js` is loaded
   - May need additional polyfills

2. **Show upgrade message:**
   ```html
   <!--[if IE]>
   <div class="ie-warning">
     Please upgrade to a modern browser for the best experience.
   </div>
   <![endif]-->
   ```

3. **Use Flexbox instead of Grid:**
   - IE11 doesn't support CSS Grid
   - Fallback to Flexbox

---

## Email Configuration Issues

### Issue: Gmail blocks emails

**Symptoms:**
- Using Gmail SMTP
- Emails not sending

**Solutions:**

1. **Enable "Less secure app access":**
   - Not recommended, use App Passwords instead

2. **Create App Password:**
   - Google Account → Security → App Passwords
   - Generate password for "Mail"
   - Use in SMTP configuration

3. **Use OAuth2:**
   - More secure than password
   - Requires additional setup

### Issue: Emails go to spam

**Symptoms:**
- Emails send but arrive in spam folder

**Solutions:**

1. **Use SMTP instead of mail():**
   - Better deliverability
   - Proper authentication

2. **Add SPF record:**
   ```
   v=spf1 include:_spf.google.com ~all
   ```

3. **Add DKIM signature:**
   - Configure in email provider
   - Verifies email authenticity

4. **Use professional email:**
   - Avoid free email providers for sending
   - Use domain email (you@yourdomain.com)

---

## General Debugging Tips

### Enable Error Reporting

```php
// Add to top of PHP files during development
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Check Browser Console

1. Open DevTools (F12)
2. Go to Console tab
3. Look for red error messages
4. Read error message and line number

### Check Network Requests

1. Open DevTools (F12)
2. Go to Network tab
3. Reload page
4. Check for failed requests (red)
5. Click request to see details

### Test in Incognito Mode

- Eliminates browser extension interference
- Fresh cache and cookies
- Helps isolate issues

### Clear Cache

```javascript
// Hard reload in browser
// Windows: Ctrl + Shift + R
// Mac: Cmd + Shift + R
```

### Check File Permissions

```bash
# Directories: 755
chmod 755 directory_name

# Files: 644
chmod 644 file_name.php
```

### Validate HTML/CSS/JS

- [W3C HTML Validator](https://validator.w3.org/)
- [CSS Validator](https://jigsaw.w3.org/css-validator/)
- [JSHint](https://jshint.com/)

### Use Browser DevTools

- **Elements**: Inspect HTML/CSS
- **Console**: JavaScript errors
- **Network**: HTTP requests
- **Performance**: Speed analysis
- **Application**: Storage, cache

### Check Server Logs

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# PHP error log
tail -f /var/log/php_errors.log
```

---

## Still Having Issues?

If you've tried the solutions above and still have problems:

1. **Check browser console** for specific error messages
2. **Test in different browser** to isolate browser-specific issues
3. **Test on different device** to rule out device-specific problems
4. **Review code changes** - what changed before it broke?
5. **Search error message** online - others may have solved it
6. **Ask for help** with specific error messages and steps to reproduce

### Useful Information to Provide

When asking for help, include:

- Browser and version
- Operating system
- Specific error messages from console
- Steps to reproduce the issue
- What you've already tried
- Screenshots if applicable

---

**Last Updated**: [Current Date]
