# Database Exports

SQL dumps of the `portfolio_laravel` MySQL database.

## Setup on another device

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or Laragon) installed and **MySQL running**
- [Composer](https://getcomposer.org/) installed
- [Node.js](https://nodejs.org/) installed (for Puppeteer/PDF export)
- A [Groq API key](https://console.groq.com/) (free) for AI features

### Steps

1. Clone or copy the project folder to the new device
2. Double-click **`setup.bat`** in the project root
3. Follow the prompts:
   - MySQL username/password (default: root / blank)
   - Database name (default: `portfolio_laravel`)
   - Groq API key (get one free at https://console.groq.com)
   - Chrome path for PDF export (e.g. `C:/Program Files/Google/Chrome/Application/chrome.exe`)
4. The script opens `http://localhost:8000` automatically when done

That's it.

---

## What setup.bat does automatically

- Finds PHP and MySQL (XAMPP / Laragon / PATH)
- Creates the database
- Imports the latest SQL dump from this folder
- Configures `.env` with DB credentials and API keys
- Runs `composer install`
- Runs `npm install` (for Puppeteer)
- Generates the app key
- Creates the storage symlink
- Clears caches
- Starts the dev server at `http://localhost:8000`

---

## Generating a fresh SQL dump

Run from the project root before sharing:

```bat
E:\xampp\mysql\bin\mysqldump.exe -u root --no-tablespaces portfolio_laravel > database\exports\portfolio_laravel_%date:~10,4%-%date:~4,2%-%date:~7,2%.sql
```

Or use phpMyAdmin → Export → Quick → SQL format.

---

## API Keys needed

| Key | Where to get | Used for |
|-----|-------------|----------|
| `GROQ_API_KEY` | https://console.groq.com | Chatbot + AI Insights |
| `BROWSERSHOT_CHROME_PATH` | Local Chrome install | PDF Export |
