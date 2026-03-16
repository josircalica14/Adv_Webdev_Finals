# Database Exports

SQL dumps of the `portfolio_laravel` MySQL database.

## To run on another device

1. Copy the entire project folder to the new device
2. Make sure **XAMPP** (or Laragon) is installed and **MySQL is running**
3. Double-click **`setup.bat`** in the project root
4. Follow the prompts (just press Enter to accept defaults)

That's it — the script handles everything automatically.

---

## What setup.bat does

- Finds PHP and MySQL automatically (XAMPP / Laragon / PATH)
- Creates the database
- Imports the latest SQL dump from this folder
- Configures `.env` with your DB credentials
- Runs `composer install`
- Generates the app key
- Creates the storage symlink
- Opens `http://localhost:8000` in your browser

## Generating a fresh dump

Run this from the project root when you want to export again:

```bat
E:\xampp\mysql\bin\mysqldump.exe -u root --no-tablespaces portfolio_laravel > database\exports\portfolio_laravel_%date:~10,4%-%date:~4,2%-%date:~7,2%.sql
```
