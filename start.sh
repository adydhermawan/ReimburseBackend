#!/bin/bash

# Start development server with Traefik
echo "🐳 Starting Docker containers with Traefik..."
docker-compose up -d

echo ""
echo "✅ Development server started!"
echo ""
echo "📍 Access points (via Traefik):"
echo "   - API:              http://reimburse.localhost:8888"
echo "   - API Categories:   http://reimburse.localhost:8888/api/categories"
echo "   - Admin Panel:      http://reimburse.localhost:8888/admin"
echo "   - Adminer (DB GUI): http://adminer.localhost:8888"
echo "   - Traefik Dashboard: http://localhost:8889"
echo ""
echo "🔌 Direct ports:"
echo "   - MySQL: localhost:33060"
echo "   - Redis: localhost:63790"
echo ""
echo "🔧 Useful commands:"
echo "   - View logs:     docker-compose logs -f app"
echo "   - Run artisan:   docker-compose exec app php artisan [command]"
echo "   - Stop:          docker-compose down"
echo ""
