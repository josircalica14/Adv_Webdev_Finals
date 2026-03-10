# Design Document

## Overview

This design transforms the existing PHP-based portfolio into a modern BSIT student portfolio featuring skills showcase, project gallery, contact functionality, resume downloads, Three.js 3D visualizations, and AI chatbot integration. The solution maintains the current aesthetic while adding professional features expected in technical portfolios.

### Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **3D Graphics**: Three.js for interactive visualizations
- **AI Integration**: OpenAI GPT API or Gemini API (recommended options)
- **Backend**: PHP for form handling and file serving
- **Styling**: Existing CSS with CSS Grid and Flexbox enhancements

### AI Integration Recommendation

**Recommended Option: Google Gemini API**
- Free tier with generous limits (60 requests/minute)
- Excellent for portfolio Q&A and contextual responses
- Easy integration with JavaScript fetch API
- Alternative: OpenAI GPT-3.5-turbo (requires API key with usage costs)

## Architecture

### High-Level Structure

```
Portfolio System
├── Homepage (index.php)
│   ├── Hero Section (existing)
│   ├── Skills Section (new)
│   ├── Projects Gallery (enhanced)
│   └── Three.js 3D Background (new)
├── About Page (about.php - enhanced)
├── Contact Page (contact.php - enhanced with backend)
├── Projects Detail Modal (new component)
├── AI Chatbot Widget (new - floating)
└── Resume Download Handler (new)
```


### Component Architecture

**1. Skills Section Component**
- Grid-based layout with skill cards
- Each card contains: icon, skill name, proficiency bar, category tag
- Responsive: 4 columns (desktop) → 2 columns (tablet) → 1 column (mobile)
- Data stored in JavaScript object for easy updates

**2. Projects Gallery Component**
- Card-based grid layout replacing current static images
- Filter buttons for technology categories
- Modal overlay for detailed project view
- Lazy loading for project images

**3. AI Chatbot Widget**
- Floating button (bottom-right corner)
- Expandable chat interface (320px × 500px)
- Message history with scrolling
- Context-aware responses based on portfolio data

**4. Three.js 3D Visualization**
- Animated geometric shapes or particle system
- Mouse-interactive camera controls
- Performance-optimized with requestAnimationFrame
- Fallback to static gradient for unsupported devices

**5. Resume Download Module**
- Button component with download icon
- Server-side PHP handler for file serving
- Download tracking via simple counter file
- PDF validation and security checks

## Components and Interfaces

### Skills Section Interface

```javascript
// skills-data.js
const skillsData = {
  "Programming Languages": [
    { name: "JavaScript", level: 90, icon: "fab fa-js" },
    { name: "Python", level: 85, icon: "fab fa-python" },
    { name: "PHP", level: 80, icon: "fab fa-php" }
  ],
  "Frameworks & Libraries": [
    { name: "React.js", level: 75, icon: "fab fa-react" },
    { name: "Bootstrap", level: 90, icon: "fab fa-bootstrap" }
  ],
  "Tools & Technologies": [
    { name: "Git", level: 85, icon: "fab fa-git-alt" },
    { name: "Three.js", level: 70, icon: "fas fa-cube" }
  ]
};
```


### Projects Gallery Interface

```javascript
// projects-data.js
const projectsData = [
  {
    id: 1,
    title: "E-Commerce Platform",
    description: "Full-stack online shopping system",
    thumbnail: "images/project1.jpg",
    technologies: ["React", "Node.js", "MongoDB"],
    category: "Web Development",
    liveUrl: "https://demo.example.com",
    githubUrl: "https://github.com/user/project",
    detailedDescription: "Comprehensive description...",
    features: ["User authentication", "Payment integration", "Admin dashboard"]
  }
  // Additional projects...
];
```

### AI Chatbot Interface

```javascript
// chatbot-config.js
const chatbotConfig = {
  apiEndpoint: "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent",
  apiKey: "YOUR_GEMINI_API_KEY",
  systemContext: `You are an AI assistant for [Student Name]'s portfolio. 
    Answer questions about their skills, projects, education, and experience.
    Skills: JavaScript, Python, PHP, React, Three.js
    Projects: [List key projects]
    Education: BSIT Student
    Be helpful, professional, and concise.`
};
```

### Contact Form Backend

```php
// contact-handler.php
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize($_POST['contact-name']);
    $email = filter_var($_POST['contact-email'], FILTER_VALIDATE_EMAIL);
    $message = sanitize($_POST['contact-project']);
    
    if ($email && $name && $message) {
        // Send email using PHP mail() or PHPMailer
        $to = "student@example.com";
        $subject = "Portfolio Contact: " . $name;
        $body = "From: $name\nEmail: $email\n\n$message";
        
        if (mail($to, $subject, $body)) {
            echo json_encode(["success" => true]);
        }
    }
}
?>
```


## Data Models

### Skill Model
```javascript
{
  name: String,           // Skill name (e.g., "JavaScript")
  level: Number,          // Proficiency 0-100
  icon: String,           // Font Awesome class or image path
  category: String,       // Category grouping
  yearsExperience: Number // Optional: years of experience
}
```

### Project Model
```javascript
{
  id: Number,
  title: String,
  description: String,
  thumbnail: String,      // Image path
  technologies: Array,    // Array of tech strings
  category: String,       // Filter category
  liveUrl: String,        // Optional: live demo URL
  githubUrl: String,      // Optional: repository URL
  detailedDescription: String,
  features: Array,        // Key features list
  screenshots: Array      // Additional images
}
```

### Chat Message Model
```javascript
{
  id: String,             // Unique message ID
  role: String,           // "user" or "assistant"
  content: String,        // Message text
  timestamp: Date
}
```

### Resume Download Log
```javascript
{
  timestamp: Date,
  ipAddress: String,      // Optional: for analytics
  userAgent: String       // Optional: device info
}
```

## Error Handling

### Contact Form Errors
- **Validation Errors**: Display inline error messages for invalid email, empty fields
- **Server Errors**: Show user-friendly message with retry button
- **Network Errors**: Detect offline status and inform user
- **Success State**: Clear form and show confirmation message for 5 seconds

### AI Chatbot Errors
- **API Timeout**: Display "Taking longer than expected..." after 5 seconds
- **API Failure**: Show fallback message directing to contact form
- **Rate Limiting**: Inform user to wait before next message
- **Invalid Response**: Gracefully handle and request clarification


### Three.js Rendering Errors
- **WebGL Not Supported**: Display static background gradient
- **Performance Issues**: Reduce particle count or animation complexity
- **Mobile Devices**: Simplify 3D scene or disable on low-end devices

### Resume Download Errors
- **File Not Found**: Display error message and contact information
- **Download Failure**: Provide alternative direct link
- **Large File Size**: Compress PDF or warn user about file size

## Testing Strategy

### Unit Testing
- **Skills Rendering**: Verify correct skill data display and proficiency bars
- **Project Filtering**: Test filter logic for all categories
- **Form Validation**: Test email validation, required fields, character limits
- **Data Models**: Validate data structure integrity

### Integration Testing
- **Contact Form Submission**: End-to-end test from form to email delivery
- **AI Chatbot API**: Test API calls with various question types
- **Resume Download**: Verify file serving and download tracking
- **Three.js Initialization**: Test 3D scene loading and interaction

### Responsive Testing
- **Breakpoints**: Test at 320px, 768px, 1024px, 1920px widths
- **Touch Interactions**: Verify mobile gesture support for 3D elements
- **Navigation**: Test mobile menu functionality
- **Performance**: Measure load times on 3G, 4G, and WiFi

### Cross-Browser Testing
- **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **WebGL Support**: Test Three.js fallback on unsupported browsers
- **CSS Grid/Flexbox**: Verify layout consistency across browsers

### Accessibility Testing
- **Keyboard Navigation**: Ensure all interactive elements are keyboard accessible
- **Screen Readers**: Test with NVDA/JAWS for proper ARIA labels
- **Color Contrast**: Verify WCAG AA compliance for text readability
- **Focus Indicators**: Ensure visible focus states for all interactive elements


## Implementation Details

### File Structure
```
portfolio/
├── index.php                    # Homepage with skills & projects
├── about.php                    # Enhanced about page
├── contact.php                  # Contact form page
├── resume-download.php          # Resume download handler
├── contact-handler.php          # Form submission backend
├── includes/
│   ├── header.php              # Existing header
│   ├── nav.php                 # Enhanced navigation
│   └── footer.php              # Existing footer
├── css/
│   ├── style.css               # Existing styles
│   ├── skills.css              # New: Skills section styles
│   ├── projects.css            # New: Projects gallery styles
│   └── chatbot.css             # New: AI chatbot styles
├── js/
│   ├── skills.js               # Skills section logic
│   ├── projects.js             # Projects gallery & filtering
│   ├── chatbot.js              # AI chatbot functionality
│   ├── three-background.js     # Three.js 3D visualization
│   └── main.js                 # General utilities
├── data/
│   ├── skills-data.js          # Skills configuration
│   └── projects-data.js        # Projects configuration
├── images/
│   ├── projects/               # Project thumbnails
│   └── skills/                 # Skill icons (if custom)
└── assets/
    └── resume.pdf              # Student resume file
```

### Navigation Enhancement
Update `includes/nav.php` to add Skills link:
```php
<div class="nav-links">
    <li><a href="index.php">HOME</a></li>
    <li><a href="index.php#skills">SKILLS</a></li>
    <li><a href="index.php#projects">PROJECTS</a></li>
    <li><a href="about.php">ABOUT</a></li>
    <li><a href="contact.php">CONTACT</a></li>
    <li><a href="resume-download.php" class="resume-link">
        <i class="fas fa-download"></i> RESUME
    </a></li>
</div>
```

### Performance Optimization
- **Lazy Loading**: Implement Intersection Observer for project images
- **Code Splitting**: Load Three.js and chatbot scripts only when needed
- **Image Optimization**: Compress project thumbnails to WebP format
- **Caching**: Set appropriate cache headers for static assets
- **Minification**: Minify CSS and JavaScript for production

### Security Considerations
- **Form Validation**: Server-side validation for all contact form inputs
- **XSS Prevention**: Sanitize all user inputs before processing
- **CSRF Protection**: Implement token-based CSRF protection for forms
- **API Key Security**: Store API keys in environment variables or config files (not in client-side code)
- **Rate Limiting**: Limit contact form submissions to prevent spam
- **File Access**: Validate resume file access and prevent directory traversal


## Design Patterns

### Skills Section Design
- **Visual Style**: Card-based grid matching your existing aesthetic (#eaeaea background, black borders)
- **Proficiency Display**: Animated progress bars (0-100%) using your existing color scheme
- **Color Coding**: Uses your existing palette (#d6a5ad accent, #0f0f0f text)
- **Typography**: Maintains your current font stack (Arial, Helvetica, sans-serif)
- **Icons**: Font Awesome icons (already included in your header.php)
- **Animation**: Fade-in on scroll using Intersection Observer
- **Spacing**: Follows your existing 60px padding pattern

### Projects Gallery Design
- **Layout**: CSS Grid matching your existing gallery (3 columns → 1 on mobile)
- **Filter UI**: Buttons styled like your existing .btn-folders (black border, rounded)
- **Card Design**: Enhances your current gallery images with overlay info on hover
- **Border Style**: Maintains your #0f0f0f 1px solid borders
- **Modal**: Overlay using your existing color scheme and border radius patterns
- **Transitions**: Smooth fade and scale animations matching your site's feel

### AI Chatbot Design
- **Trigger**: Floating circular button with chat icon (bottom-right, styled like your .account-btn)
- **Chat Window**: Card-style interface matching your existing .drop-zone aesthetic
- **Background**: Uses your #eaeaea background color
- **Accents**: Your #d6a5ad pink accent for AI messages
- **Messages**: User messages in black, AI in your accent color
- **Typography**: Same font family and weights as your existing site
- **Borders**: Consistent with your 1px solid #0f0f0f style
- **Typing Indicator**: Animated dots in your color scheme

### Three.js Visualization Options

**Option 1: Particle Field**
- Floating particles with mouse interaction
- Particles connect when close (network effect)
- Subtle animation, non-distracting

**Option 2: Geometric Shapes**
- Rotating 3D geometric objects (cubes, spheres)
- Wireframe or solid with transparency
- Camera follows mouse movement

**Option 3: Wave Animation**
- Undulating plane with vertex displacement
- Gradient coloring matching site theme
- Smooth, calming motion

**Recommended**: Option 1 (Particle Field) - visually impressive, performant, and professional

## AI Integration Implementation

### Gemini API Integration (Recommended)

**Setup Steps:**
1. Get free API key from Google AI Studio
2. Create chatbot.js with fetch API calls
3. Implement context injection with portfolio data
4. Handle streaming responses for better UX

**Sample Implementation:**
```javascript
async function sendMessageToAI(userMessage) {
  const response = await fetch(
    `https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=${API_KEY}`,
    {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        contents: [{
          parts: [{ text: systemContext + "\n\nUser: " + userMessage }]
        }]
      })
    }
  );
  const data = await response.json();
  return data.candidates[0].content.parts[0].text;
}
```

### Alternative: OpenAI Integration
- Requires paid API key
- Better conversational quality
- Higher cost per request
- Similar implementation pattern

### Fallback Strategy
If AI API is unavailable:
- Display pre-written FAQ responses
- Direct users to contact form
- Show "AI temporarily unavailable" message


## Style Preservation Strategy

### Existing Design Elements to Maintain
1. **Color Palette**:
   - Background: #eaeaea (light gray)
   - Surface: #f4f4f4
   - Accent: #d6a5ad (pink)
   - Text: #0f0f0f (near black)
   - Borders: #0f0f0f 1px solid

2. **Typography**:
   - Font Family: Arial, Helvetica, sans-serif
   - Heading weights: 900 (hero), 700 (sections), 600 (nav)
   - Body text: 300-400 weight

3. **Layout Patterns**:
   - Padding: 60px horizontal, 40px vertical (desktop)
   - Padding: 20px horizontal (mobile)
   - Border radius: 8px (buttons), 20px (account-btn)
   - Grid gaps: 20px

4. **Interactive Elements**:
   - Button style: transparent background, black border, hover fills black
   - Link hover: maintains black color (no color change)
   - Transitions: smooth, subtle

5. **Existing Components to Enhance (Not Replace)**:
   - Navbar structure (add items, keep style)
   - Hero section (add 3D background layer)
   - Gallery grid (enhance with filtering, keep layout)
   - Contact form (add backend, keep styling)
   - Footer (keep as-is)

### New Components Style Guidelines
All new components (Skills, Chatbot, Resume button) will:
- Use the existing CSS variable patterns
- Match border styles and radius values
- Follow the same spacing rhythm
- Use the established color palette
- Maintain the minimalist, clean aesthetic
- Respect the existing responsive breakpoints (768px)

