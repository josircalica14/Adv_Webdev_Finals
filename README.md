# Student Portfolio Platform

A modern web application for students to create, manage, and showcase their portfolios with customizable PDF exports.

## Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx) or XAMPP/WAMP

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/josircalica14/Adv_Webdev_Finals.git
cd Adv_Webdev_Finals
```

2. **Install dependencies**
```bash
composer install
```

3. **Run automatic setup**
```bash
php setup.php
```

4. **Create database**
```sql
CREATE DATABASE portfolio_platform;
```

5. **Import database**
```bash
mysql -u root portfolio_platform < database/portfolio_platform_export.sql
```

6. **Start your web server**
- **XAMPP/WAMP**: Place project in `htdocs` folder
- **Built-in PHP server**: `php -S localhost:8000`

7. **Access the application**
```
http://localhost/Adv_Webdev_Finals
```
or
```
http://localhost:8000
```

### Default Login Credentials

**Test User:**
- Username: `testuser1`
- Password: `password123`

## Features

- User authentication and registration
- Portfolio management (projects, achievements, milestones)
- Public portfolio showcase
- Customizable PDF export with live preview
- Split-screen PDF customizer
- Responsive design
- File uploads and management

## Configuration

If you need to customize database settings, edit `config/app.config.php` after running setup.

## Troubleshooting

**Database connection error?**
- Check credentials in `config/app.config.php`
- Ensure MySQL is running

**Permission errors?**
- Run `php setup.php` again
- Check folder permissions for `uploads/`, `temp/`, `logs/`

**PDF export not working?**
- TCPDF is already included via Composer
- Run `composer install` if missing

## Tech Stack

- PHP 7.4+
- MySQL
- JavaScript (Vanilla)
- CSS3
- TCPDF for PDF generation
- Three.js for 3D backgrounds

## License

This project is for educational purposes.
