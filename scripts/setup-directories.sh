#!/bin/bash

# Directory Setup Script for Portfolio Platform
# This script creates necessary directories with proper permissions

set -e

echo "Setting up Portfolio Platform directories..."

# Define base directory (adjust as needed)
BASE_DIR="/var/www/portfolio-platform"

# Create directories
echo "Creating directories..."
mkdir -p "$BASE_DIR/uploads"
mkdir -p "$BASE_DIR/uploads/thumbnails"
mkdir -p "$BASE_DIR/uploads/profile-photos"
mkdir -p "$BASE_DIR/uploads/portfolio-items"
mkdir -p "$BASE_DIR/temp"
mkdir -p "$BASE_DIR/logs"
mkdir -p "$BASE_DIR/cache"
mkdir -p "$BASE_DIR/config"

# Set ownership (adjust user:group as needed for your web server)
# Common options: www-data:www-data (Ubuntu/Debian), apache:apache (CentOS/RHEL), nginx:nginx
WEB_USER="www-data"
WEB_GROUP="www-data"

echo "Setting ownership to $WEB_USER:$WEB_GROUP..."
chown -R "$WEB_USER:$WEB_GROUP" "$BASE_DIR/uploads"
chown -R "$WEB_USER:$WEB_GROUP" "$BASE_DIR/temp"
chown -R "$WEB_USER:$WEB_GROUP" "$BASE_DIR/logs"
chown -R "$WEB_USER:$WEB_GROUP" "$BASE_DIR/cache"

# Set permissions
echo "Setting directory permissions..."
# Uploads directory - read/write for web server
chmod 755 "$BASE_DIR/uploads"
chmod 755 "$BASE_DIR/uploads/thumbnails"
chmod 755 "$BASE_DIR/uploads/profile-photos"
chmod 755 "$BASE_DIR/uploads/portfolio-items"

# Temp directory - read/write for web server
chmod 755 "$BASE_DIR/temp"

# Logs directory - read/write for web server
chmod 755 "$BASE_DIR/logs"

# Cache directory - read/write for web server
chmod 755 "$BASE_DIR/cache"

# Config directory - restricted access
chmod 750 "$BASE_DIR/config"

# Secure config files (if they exist)
if [ -f "$BASE_DIR/config/app.config.php" ]; then
    echo "Securing configuration file..."
    chmod 600 "$BASE_DIR/config/app.config.php"
    chown "$WEB_USER:$WEB_GROUP" "$BASE_DIR/config/app.config.php"
fi

if [ -f "$BASE_DIR/.env" ]; then
    echo "Securing .env file..."
    chmod 600 "$BASE_DIR/.env"
    chown "$WEB_USER:$WEB_GROUP" "$BASE_DIR/.env"
fi

# Create .htaccess files to prevent direct access to sensitive directories
echo "Creating .htaccess protection..."

cat > "$BASE_DIR/uploads/.htaccess" << 'EOF'
# Prevent PHP execution in uploads directory
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Allow image and document access
<FilesMatch "\.(jpg|jpeg|png|gif|webp|pdf)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOF

cat > "$BASE_DIR/logs/.htaccess" << 'EOF'
# Deny all access to logs directory
Order Deny,Allow
Deny from all
EOF

cat > "$BASE_DIR/temp/.htaccess" << 'EOF'
# Deny all access to temp directory
Order Deny,Allow
Deny from all
EOF

cat > "$BASE_DIR/cache/.htaccess" << 'EOF'
# Deny all access to cache directory
Order Deny,Allow
Deny from all
EOF

cat > "$BASE_DIR/config/.htaccess" << 'EOF'
# Deny all access to config directory
Order Deny,Allow
Deny from all
EOF

echo "Directory setup complete!"
echo ""
echo "Directory structure:"
echo "  $BASE_DIR/uploads - File uploads (755)"
echo "  $BASE_DIR/temp - Temporary files (755)"
echo "  $BASE_DIR/logs - Application logs (755)"
echo "  $BASE_DIR/cache - Cache files (755)"
echo "  $BASE_DIR/config - Configuration files (750)"
echo ""
echo "IMPORTANT: Verify that the web server user ($WEB_USER) has appropriate permissions."
