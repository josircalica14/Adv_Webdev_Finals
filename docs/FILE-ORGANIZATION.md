# File Organization

This document describes the organized file structure of the Student Portfolio Platform.

## Directory Structure

```
AdvWebDevFinals/
├── index.php                    # Main landing page
├── README.md                    # Project overview
├── QUICK-START.md               # Quick reference guide
├── pages/                       # All user-facing pages
│   ├── auth/                    # Authentication pages
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── password-reset.php
│   │   └── password-reset-request.php
│   ├── dashboard/               # Dashboard pages
│   │   ├── dashboard.php
│   │   ├── profile.php
│   │   ├── settings.php
│   │   ├── change-password.php
│   │   ├── change-username.php
│   │   ├── customize-portfolio.php
│   │   └── export-portfolio.php
│   ├── portfolio/               # Portfolio management pages
│   │   ├── view.php            # Public portfolio view
│   │   ├── add-item.php
│   │   ├── edit-item.php
│   │   └── delete-item.php
│   ├── showcase.php             # Student showcase
│   ├── about.php
│   ├── contact.php
│   ├── contact-handler.php
│   └── resume-download.php
├── includes/                    # PHP classes and utilities
│   ├── Auth/                    # Authentication classes
│   ├── Portfolio/               # Portfolio management
│   ├── Customization/           # Theme customization
│   ├── Email/                   # Email services
│   ├── Cache/                   # Caching system
│   ├── Admin/                   # Admin functionality
│   ├── Export/                  # PDF export
│   ├── Profile/                 # Profile management
│   ├── Showcase/                # Showcase functionality
│   ├── header.php               # Common header
│   ├── nav.php                  # Navigation component
│   └── footer.php               # Common footer
├── css/                         # Stylesheets (root level for easy access)
├── js/                          # JavaScript files (root level for easy access)
├── data/                        # Static data files
├── config/                      # Configuration files
├── database/                    # Database migrations and seeds
├── auth/                        # Auth utilities
├── assets/                      # Static assets
├── uploads/                     # User uploaded files
├── cache/                       # Cache storage
├── logs/                        # Application logs
├── tests/                       # Test files
├── scripts/                     # Utility scripts
├── docs/                        # Documentation (all .md files)
└── vendor/                      # Composer dependencies
```

## Page Organization

### Authentication Flow
- `pages/auth/login.php` - User login
- `pages/auth/register.php` - New user registration
- `pages/auth/password-reset-request.php` - Request password reset
- `pages/auth/password-reset.php` - Reset password with token

### Dashboard Pages
- `pages/dashboard/dashboard.php` - Main dashboard
- `pages/dashboard/profile.php` - Edit user profile
- `pages/dashboard/settings.php` - Account settings
- `pages/dashboard/change-password.php` - Change password
- `pages/dashboard/change-username.php` - Change username
- `pages/dashboard/customize-portfolio.php` - Customize portfolio theme
- `pages/dashboard/export-portfolio.php` - Export portfolio as PDF

### Portfolio Management
- `pages/portfolio/view.php` - Public portfolio view (formerly portfolio-view.php)
- `pages/portfolio/add-item.php` - Add portfolio item
- `pages/portfolio/edit-item.php` - Edit portfolio item
- `pages/portfolio/delete-item.php` - Delete portfolio item

### Public Pages
- `index.php` - Homepage with featured portfolios
- `pages/showcase.php` - Browse all student portfolios
- `pages/about.php` - About page
- `pages/contact.php` - Contact form
- `pages/contact-handler.php` - Contact form handler
- `pages/resume-download.php` - Download resume

## Benefits of This Organization

1. **Clear Separation**: Pages are organized by functionality
2. **Easy Navigation**: Developers can quickly find related files
3. **Scalability**: Easy to add new pages in appropriate directories
4. **Maintainability**: Logical grouping makes updates easier
5. **Security**: Sensitive files remain in includes/ directory

## Migration Notes

All internal links and includes have been automatically updated to reflect the new file locations.
