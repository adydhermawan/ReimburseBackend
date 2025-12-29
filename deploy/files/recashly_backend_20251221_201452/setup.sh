#!/bin/bash

# Reimburse Backend - Initial Setup Script
set -e

echo "🚀 Setting up Reimburse Backend with Traefik..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker Desktop and try again."
    exit 1
fi

# Check hosts entry
if ! grep -q "reimburse.localhost" /etc/hosts 2>/dev/null; then
    echo "⚠️  Please add hosts entry:"
    echo "   sudo sh -c 'echo \"127.0.0.1 reimburse.localhost adminer.localhost\" >> /etc/hosts'"
    echo ""
fi

# Copy .env if not exists
if [ ! -f .env ]; then
    echo "📋 Copying .env.example to .env..."
    cp .env.example .env
fi

# Create required directories locally
echo "📁 Creating required directories..."
mkdir -p bootstrap/cache
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 777 bootstrap/cache
chmod -R 777 storage

echo "📦 Building Docker containers..."
docker-compose build --no-cache

echo "🐳 Starting containers..."
docker-compose up -d

echo "⏳ Waiting for MySQL to be ready..."
sleep 15

# Run composer install inside container
echo "📚 Installing dependencies..."
docker-compose exec -T app composer install --no-interaction --prefer-dist --no-scripts

# Run post-install scripts manually
echo "🔧 Running Laravel setup..."
docker-compose exec -T app php artisan package:discover --ansi || true

# Generate app key
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate --force

# Create storage link
echo "🔗 Creating storage symlink..."
docker-compose exec -T app php artisan storage:link || true

# Run migrations
echo "🗄️ Running migrations..."
docker-compose exec -T app php artisan migrate --force

# Seed database
echo "🌱 Seeding database..."
docker-compose exec -T app php artisan db:seed --force

echo ""
echo "✅ Setup complete!"
echo ""
echo "📍 Access points:"
echo "   - API: http://reimburse.localhost:8888/api/categories"
echo "   - Adminer: http://adminer.localhost:8888"
echo "   - Traefik: http://localhost:8889"
echo ""
