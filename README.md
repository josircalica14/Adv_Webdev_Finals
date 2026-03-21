# Portfolio Platform

A student portfolio showcase for BSIT & CSE students — built with Laravel. Students can create portfolios, showcase projects, customize their page, export to PDF, and get AI-powered feedback.

---

## Features

- Public showcase with search and filter
- Student portfolio pages with customizable themes, fonts, and colors
- PDF export with Browsershot (Puppeteer)
- AI Insights powered by Groq (llama-3.3-70b-versatile)
- AI Chatbot assistant with live student directory context
- Portfolio completeness score
- Admin panel for content moderation
- Email verification on registration

---

## Quick Setup (Windows)

**Requirements:**
- [XAMPP](https://www.apachefriends.org/) with MySQL running
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) (for PDF export via Puppeteer)
- [Groq API key](https://console.groq.com/) — free, for AI features

**Steps:**

1. Clone the repo
   ```bash
   git clone https://github.com/josircalica14/Adv_Webdev_Finals.git
   cd Adv_Webdev_Finals
   ```

2. Start **MySQL** in XAMPP Control Panel

3. Double-click **`setup.bat`** and follow the prompts:
   - MySQL credentials (default: root / blank)
   - Database name (default: `portfolio_laravel`)
   - Groq API key
   - Chrome path for PDF export (e.g. `C:/Program Files/Google/Chrome/Application/chrome.exe`)

4. The script opens `http://localhost:8000` automatically when done

> Keep the terminal window open while using the app.

---

## Environment Variables

Copy `.env.example` to `.env` and fill in:

| Variable | Description |
|----------|-------------|
| `DB_DATABASE` | MySQL database name |
| `DB_USERNAME` | MySQL username |
| `DB_PASSWORD` | MySQL password |
| `GROQ_API_KEY` | Groq API key for AI features |
| `BROWSERSHOT_CHROME_PATH` | Path to Chrome executable for PDF export |
| `APP_KEY` | Generated automatically by `setup.bat` |

---

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2
- **Frontend:** Blade templates, vanilla JS
- **Database:** MySQL
- **AI:** Groq API (`llama-3.3-70b-versatile`)
- **PDF:** Spatie Browsershot + Puppeteer
- **Storage:** Laravel local disk with storage symlink
