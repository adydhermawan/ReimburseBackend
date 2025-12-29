#!/bin/bash
# Production Deployment Script
# 1. Exports database from Docker container
# 2. Archives project files for FTP upload

echo "🚀 Starting Production Build..."

# Create deploy directory
mkdir -p deploy/sql
mkdir -p deploy/files

# 1. Export Database
echo "📦 Exporting Database from Docker..."
FILENAME="deploy/sql/reimburse_prod_$(date +%Y%m%d_%H%M%S).sql"
# Using known container name and creds from docker-compose.yml
docker exec reimburse-mysql mysqldump -u root -ppassword reimburse > "$FILENAME"

if [ $? -eq 0 ]; then
    echo "✅ Database exported to $FILENAME"
else
    echo "❌ Database export failed!"
    exit 1
fi

# 2. Optimize Code
echo "🧹 Optimizing Application..."
docker exec reimburse-app composer install --optimize-autoloader --no-dev
docker exec reimburse-app php artisan config:clear
docker exec reimburse-app php artisan route:clear
docker exec reimburse-app php artisan view:clear

# 3. Compress Files
echo "🗜️  Compressing files..."
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ARCHIVE_NAME="deploy/files/recashly_backend_$TIMESTAMP.tar.gz"

# Archive everything except ignored files
tar --exclude='.git' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='storage/*.key' \
    --exclude='deploy' \
    --exclude='.env' \
    --exclude='.env.example' \
    --exclude='docker-compose.yml' \
    --exclude='Dockerfile' \
    --exclude='.github' \
    --exclude='.idea' \
    --exclude='.vscode' \
    --exclude='worker.html' \
    -czf "$ARCHIVE_NAME" .

echo "✅ Files archived to $ARCHIVE_NAME"
echo ""
echo "📝 Deployment Instructions:"
echo "1. Upload '$ARCHIVE_NAME' to valid public_html or root folder via FTP."
echo "2. Unzip and ensure permissions (755 for storage)."
echo "3. Upload '.env.production' as '.env'."
echo "4. Import '$FILENAME' into your production database."
echo "5. Run 'post_deploy.php' if no SSH access."
