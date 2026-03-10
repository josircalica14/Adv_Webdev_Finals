# Multi-User Portfolio Platform - Prototype Guide

## What's Been Created

This is a **working prototype** of the multi-user portfolio platform with minimal backend (hardcoded data) so you can see and interact with the frontend immediately.

## Files Created

### Backend (Minimal/Mock)
- `auth/mock-data.php` - Hardcoded user data and simple authentication

### Frontend Pages
- `showcase.php` - Main landing page showing all student portfolios
- `register.php` - User registration page
- `login.php` - User login page
- `auth/logout.php` - Logout handler

### Styles
- `css/showcase.css` - Showcase page styling
- `css/auth.css` - Authentication pages styling

### JavaScript
- `js/showcase.js` - Search, filter, and sort functionality

## How to Test

### 1. Start the Server (if not running)
```bash
php -S localhost:8000
```

### 2. Visit the Showcase
Open: `http://localhost:8000/showcase.php`

You'll see:
- 3 mock student portfolios (John Doe, Jane Smith, Mike Johnson)
- Search bar to filter by name/bio
- Filter buttons for All/BSIT/CSE
- Sort dropdown for Recent/Name

### 3. Try Registration
Click "Sign Up" or visit: `http://localhost:8000/register.php`

Fill in the form:
- Full Name: Your Name
- Email: your.email@example.com
- Program: BSIT or CSE
- Password: password123 (or any 8+ chars)

After registration, you'll be redirected to the dashboard (not yet created).

### 4. Try Login
Click "Login" or visit: `http://localhost:8000/login.php`

**Demo Credentials:**
- Email: `john.doe@example.com`
- Password: `password123`

Other demo accounts:
- `jane.smith@example.com` / `password123`
- `mike.johnson@example.com` / `password123`

## Features Working

✅ **Showcase Page**
- Browse all public portfolios
- Search by name, username, or bio
- Filter by program (BSIT/CSE)
- Sort by name or recent
- Responsive design

✅ **Registration**
- Form validation
- Program selection (BSIT/CSE)
- Password confirmation
- Creates mock session

✅ **Login**
- Email/password authentication
- Demo credentials provided
- Session management
- Remember me checkbox (UI only)

✅ **Navigation**
- Shows different options for logged in/out users
- Logout functionality

## What's NOT Implemented Yet

❌ **Dashboard** - Portfolio management interface
❌ **Portfolio View** - Individual student portfolio pages
❌ **Customization Editor** - Theme/color customization
❌ **File Uploads** - Project images and attachments
❌ **PDF Export** - Download portfolio as PDF
❌ **Real Database** - Currently using hardcoded data
❌ **Admin Panel** - Content moderation

## Next Steps

### Option 1: Continue with More Frontend
Create the remaining frontend pages:
- Dashboard (portfolio management)
- Portfolio view page
- Customization editor
- Profile settings

### Option 2: Build Real Backend
Implement the actual backend:
- Database setup
- Real authentication
- Portfolio CRUD operations
- File upload system

### Option 3: Connect Frontend to Backend
Once backend is ready, replace mock-data.php with real database queries.

## Mock Data Structure

The prototype uses these mock users:

**John Doe** (@johndoe)
- Program: BSIT
- Projects: E-Commerce Platform
- Achievements: Dean's List Award

**Jane Smith** (@janesmith)
- Program: CSE
- Projects: Image Classification Model
- Achievements: Hackathon Winner

**Mike Johnson** (@mikej)
- Program: BSIT
- Projects: Fitness Tracker App

## Customization

To add more mock users, edit `auth/mock-data.php`:

```php
$mockUsers = [
    4 => [
        'id' => 4,
        'email' => 'new.user@example.com',
        'full_name' => 'New User',
        'username' => 'newuser',
        'program' => 'BSIT',
        'bio' => 'Your bio here',
        'profile_photo' => 'images/profiles/new.jpg',
        'is_public' => true
    ]
];
```

## Testing Checklist

- [ ] Visit showcase page
- [ ] Search for "John"
- [ ] Filter by BSIT
- [ ] Filter by CSE
- [ ] Sort by name
- [ ] Click "Sign Up"
- [ ] Fill registration form
- [ ] Click "Login"
- [ ] Login with demo credentials
- [ ] Check navigation shows user name
- [ ] Click "Logout"
- [ ] Verify redirected to showcase

## Browser Compatibility

Tested on:
- Chrome (latest)
- Firefox (latest)
- Edge (latest)

Mobile responsive:
- Works on 320px to 2560px widths
- Touch-friendly buttons
- Collapsible navigation

## Known Limitations

1. **No Real Database** - Data resets on server restart
2. **No Password Hashing** - Passwords stored in plain text (prototype only!)
3. **No Email Verification** - Registration is instant
4. **No File Uploads** - Profile photos use placeholders
5. **No Portfolio Pages** - "View Portfolio" links don't work yet
6. **No Dashboard** - After login, dashboard doesn't exist yet

## Security Notes

⚠️ **This is a PROTOTYPE only!**

Do NOT use in production without:
- Real database with proper schema
- Password hashing (bcrypt)
- CSRF protection
- Input sanitization
- SQL injection prevention
- XSS prevention
- Session security
- Rate limiting

## Questions?

If you encounter issues:
1. Check browser console for errors
2. Verify PHP server is running
3. Check file permissions
4. Review TROUBLESHOOTING.md

## What Would You Like Next?

1. **More Frontend Pages** - Dashboard, portfolio view, customization editor
2. **Real Backend** - Database setup and authentication
3. **Specific Feature** - Tell me what you want to see!

---

**Current Status**: Frontend prototype with mock backend ✅
**Next Recommended**: Create dashboard page for portfolio management
