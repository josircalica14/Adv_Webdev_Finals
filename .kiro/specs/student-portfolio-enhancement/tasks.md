# Implementation Plan

- [x] 1. Set up project structure and data files





  - Create js/, data/, and assets/ directories
  - Create skills-data.js with sample skills organized by category
  - Create projects-data.js with sample project entries
  - Add placeholder resume.pdf to assets/ directory
  - _Requirements: 1.1, 2.1, 4.3_

- [x] 2. Implement Skills Section





  - [x] 2.1 Create skills.css with card grid layout matching existing style


    - Use #eaeaea background, #0f0f0f borders, 60px padding
    - Implement responsive grid (4 cols → 2 cols → 1 col)
    - Add hover effects consistent with existing buttons
    - _Requirements: 1.1, 1.3, 8.1, 8.3_

  - [x] 2.2 Create skills.js to render skills dynamically


    - Read from skills-data.js and generate HTML
    - Implement animated proficiency bars (0-100%)
    - Add Intersection Observer for scroll animations
    - Group skills by category with headers
    - _Requirements: 1.2, 1.4, 1.5_

  - [x] 2.3 Add Skills section to index.php


    - Insert section between hero and projects with id="skills"
    - Include skills.css and skills.js in header
    - Add smooth scroll behavior for navigation
    - _Requirements: 1.1, 8.2_

- [x] 3. Enhance Projects Gallery





  - [x] 3.1 Create projects.css for enhanced gallery styling


    - Maintain existing 3-column grid layout
    - Add hover overlay effects for project cards
    - Style filter buttons matching .btn-folders aesthetic
    - Create modal styles for project details view
    - _Requirements: 2.1, 2.4, 8.1, 8.4_

  - [x] 3.2 Create projects.js for gallery functionality


    - Render projects from projects-data.js
    - Implement category filtering with smooth transitions
    - Create modal popup for detailed project view
    - Add lazy loading for project images
    - _Requirements: 2.2, 2.3, 2.5_


  - [x] 3.3 Update index.php projects section

    - Replace static gallery with dynamic project cards
    - Add filter buttons above gallery
    - Include projects.css and projects.js
    - Add id="projects" for navigation
    - _Requirements: 2.1, 8.2_


- [x] 4. Implement Contact Form Backend




  - [x] 4.1 Create contact-handler.php for form processing


    - Implement input sanitization and validation
    - Add email format validation
    - Configure PHP mail() or PHPMailer for sending emails
    - Return JSON response for success/error states
    - _Requirements: 3.1, 3.2, 3.3_

  - [x] 4.2 Add JavaScript for form submission handling


    - Prevent default form submission
    - Send AJAX request to contact-handler.php
    - Display success message on successful submission
    - Show error messages with retry option on failure
    - Clear form fields after successful submission
    - _Requirements: 3.4, 3.5_

  - [x] 4.3 Update contact.php form


    - Add form ID and update action attribute
    - Include form handling JavaScript
    - Add loading indicator during submission
    - Style success/error message containers
    - _Requirements: 3.1, 3.4, 3.5_

- [x] 5. Implement Resume Download Feature






  - [x] 5.1 Create resume-download.php handler

    - Validate resume file exists in assets/ directory
    - Set appropriate headers for PDF download
    - Implement download tracking (simple counter file)
    - Add security checks to prevent directory traversal
    - _Requirements: 4.2, 4.3, 4.4, 4.5_


  - [x] 5.2 Add Resume button to navigation

    - Update includes/nav.php with resume download link
    - Style button with download icon (Font Awesome)
    - Ensure responsive behavior on mobile
    - _Requirements: 4.1, 8.2_

- [x] 6. Implement Three.js 3D Visualization




  - [x] 6.1 Create three-background.js with particle field


    - Initialize Three.js scene, camera, and renderer
    - Create particle system with 200-300 particles
    - Implement mouse interaction for camera movement
    - Add particle connection lines (network effect)
    - Optimize with requestAnimationFrame
    - _Requirements: 5.1, 5.2, 5.4_

  - [x] 6.2 Add WebGL detection and fallback


    - Check for WebGL support before initializing
    - Display static gradient background if unsupported
    - Reduce particle count on mobile devices
    - Implement performance monitoring for frame rate
    - _Requirements: 5.4, 5.5, 7.5_

  - [x] 6.3 Integrate Three.js into index.php


    - Add Three.js library via CDN in header.php
    - Create canvas container in hero section
    - Position 3D background behind hero content
    - Include three-background.js script
    - _Requirements: 5.1, 8.1_


- [x] 7. Implement AI Chatbot Integration




  - [x] 7.1 Create chatbot.css for chat interface


    - Style floating trigger button (bottom-right, circular)
    - Design chat window (320px × 500px) matching site aesthetic
    - Style message bubbles (user: left, AI: right)
    - Add typing indicator animation
    - Use existing color palette (#eaeaea, #d6a5ad, #0f0f0f)
    - _Requirements: 6.1, 8.1, 8.3_

  - [x] 7.2 Create chatbot.js with Gemini API integration


    - Implement chat window toggle functionality
    - Create message sending and receiving functions
    - Integrate Google Gemini API with fetch requests
    - Build system context with portfolio information (skills, projects, education)
    - Handle API responses and display in chat
    - Add message history management
    - _Requirements: 6.2, 6.3, 6.4_

  - [x] 7.3 Add error handling and fallback responses


    - Implement timeout handling (5 second limit)
    - Add fallback message for API failures
    - Handle rate limiting gracefully
    - Provide contact form alternative when chatbot fails
    - _Requirements: 6.5_

  - [x] 7.4 Integrate chatbot into all pages


    - Add chatbot HTML structure to includes/footer.php
    - Include chatbot.css and chatbot.js in header.php
    - Ensure chatbot appears on all pages
    - Test mobile responsiveness
    - _Requirements: 6.1, 7.1, 7.3_

- [x] 8. Responsive Design and Mobile Optimization





  - [x] 8.1 Update responsive breakpoints for new components


    - Add mobile styles for skills grid (1 column)
    - Adjust project filter buttons for mobile
    - Optimize chatbot size for small screens
    - Ensure Three.js canvas scales properly
    - _Requirements: 7.1, 7.2, 7.3_

  - [x] 8.2 Optimize touch interactions


    - Ensure all buttons meet 44x44px touch target minimum
    - Test Three.js touch gestures on mobile
    - Verify mobile navigation menu functionality
    - Test chatbot on touch devices
    - _Requirements: 7.3_

  - [x] 8.3 Performance optimization for mobile


    - Implement lazy loading for project images
    - Reduce Three.js particle count on mobile
    - Minify CSS and JavaScript files
    - Compress images to WebP format
    - Test load times on 4G connection
    - _Requirements: 7.4, 7.5_


- [x] 9. Navigation and Smooth Scrolling




  - [x] 9.1 Update includes/nav.php with new menu items

    - Add "SKILLS" link pointing to index.php#skills
    - Add "PROJECTS" link pointing to index.php#projects
    - Update "RESUME" link with download icon
    - Maintain existing navigation style
    - _Requirements: 8.2_

  - [x] 9.2 Implement smooth scroll behavior


    - Add CSS scroll-behavior: smooth to html element
    - Create JavaScript smooth scroll for anchor links
    - Handle navigation highlighting for active section
    - _Requirements: 8.2_

- [x] 10. Final Integration and Polish




  - [x] 10.1 Update includes/header.php with new dependencies


    - Add Three.js CDN link
    - Include all new CSS files (skills, projects, chatbot)
    - Include all new JavaScript files
    - Ensure proper loading order
    - _Requirements: 8.1, 8.4_

  - [x] 10.2 Create configuration file for easy customization


    - Create config.js with API keys, email settings, colors
    - Document how to update skills and projects data
    - Add comments for customization points
    - _Requirements: 8.4_

  - [x] 10.3 Add loading states and animations


    - Implement page load animations for new sections
    - Add skeleton loaders for async content
    - Ensure smooth transitions between states
    - _Requirements: 8.5_

  - [x] 10.4 Cross-browser testing


    - Test on Chrome, Firefox, Safari, Edge
    - Verify WebGL fallback works correctly
    - Check CSS Grid and Flexbox compatibility
    - Test form submission across browsers
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [x] 10.5 Accessibility improvements


    - Add ARIA labels to interactive elements
    - Ensure keyboard navigation works for all features
    - Test with screen readers (NVDA/JAWS)
    - Verify color contrast meets WCAG AA standards
    - Add focus indicators to all interactive elements
    - _Requirements: 7.3_

  - [x] 10.6 Create documentation


    - Write README with setup instructions
    - Document how to get Gemini API key
    - Explain how to customize skills and projects
    - Add troubleshooting guide for common issues
    - _Requirements: 6.3, 8.4_

