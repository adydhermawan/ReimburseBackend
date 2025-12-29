# Recashly Backend

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

[English](#english) | [Bahasa Indonesia](#bahasa-indonesia)

---

<a name="english"></a>
## 🇬🇧 English

**Recashly Backend** is the server-side application for the Recashly system, built with **Laravel 11**, **FilamentPHP v3**, and **Docker**. It provides a robust API for the mobile app and a powerful Administration Panel for managing data, users, and reports.

### 🛠️ Tech Stack

| Component | Technology |
|---|---|
| **Framework** | Laravel 11 |
| **Admin Panel** | FilamentPHP v3 |
| **Database** | MySQL 8.0 |
| **Cache** | Redis |
| **Reverse Proxy** | Traefik v3.0 |
| **Web Server** | Nginx + PHP-FPM 8.3 |
| **API Auth** | Laravel Sanctum |

### 📋 Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- Docker Compose

### 🚀 Quick Start

#### 1. Add Hosts Entry
To access the application via custom domains locally:

```bash
sudo sh -c 'echo "127.0.0.1 recashly.localhost adminer.localhost" >> /etc/hosts'
```

#### 2. Setup & Run

```bash
cd RecashlyBackend

# Copy environment file
cp .env.example .env

# Build and start containers
docker-compose up -d --build

# Wait for MySQL to be ready (approx. 30 seconds)
sleep 30

# Install PHP dependencies
docker-compose exec app composer install

# Setup Laravel
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

#### 3. Install FilamentPHP (Admin Panel)

```bash
docker-compose exec app php artisan filament:install --panels
docker-compose exec app php artisan make:filament-user
```

### 📍 Access Points

| Service | URL |
|---|---|
| **API Base URL** | `http://recashly.localhost:8888` |
| **Admin Panel** | `http://recashly.localhost:8888/admin` |
| **Adminer (DB GUI)** | `http://adminer.localhost:8888` |
| **Traefik Dashboard** | `http://localhost:8889` |

### 🔌 API Endpoints (Overview)

Full documentation should be generated via Postman or Scribe, but here are the key endpoints:

- **Authentication**: `/api/auth/register`, `/api/auth/login`, `/api/auth/logout`, `/api/auth/me`
- **Reimbursements**: `/api/reimbursements` (GET, POST), `/api/reimbursements/{id}` (GET, PUT)
- **Reports**: `/api/reports`, `/api/reports/{id}/download`

### 🔧 Useful Commands

```bash
# View container logs
docker-compose logs -f app

# Run artisan commands
docker-compose exec app php artisan [command]

# Run migrations
docker-compose exec app php artisan migrate

# Stop containers
docker-compose down
```

---

<a name="bahasa-indonesia"></a>
## 🇮🇩 Bahasa Indonesia

**Recashly Backend** adalah aplikasi sisi server untuk sistem Recashly, dibangun menggunakan **Laravel 11**, **FilamentPHP v3**, dan **Docker**. Aplikasi ini menyediakan API yang tangguh untuk aplikasi mobile serta Panel Admin yang kuat untuk mengelola data, pengguna, dan laporan.

### 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| **Framework** | Laravel 11 |
| **Panel Admin** | FilamentPHP v3 |
| **Database** | MySQL 8.0 |
| **Cache** | Redis |
| **Reverse Proxy** | Traefik v3.0 |
| **Web Server** | Nginx + PHP-FPM 8.3 |
| **Autentikasi API** | Laravel Sanctum |

### 📋 Prasyarat

- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- Docker Compose

### 🚀 Cara Menjalankan

#### 1. Tambahkan Entry Host
Untuk mengakses aplikasi melalui domain khusus secara lokal:

```bash
sudo sh -c 'echo "127.0.0.1 recashly.localhost adminer.localhost" >> /etc/hosts'
```

#### 2. Setup & Jalankan

```bash
cd RecashlyBackend

# Salin file environment
cp .env.example .env

# Build dan jalankan container
docker-compose up -d --build

# Tunggu MySQL siap (sekitar 30 detik)
sleep 30

# Install dependensi PHP
docker-compose exec app composer install

# Setup Laravel
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

#### 3. Install FilamentPHP (Panel Admin)

```bash
docker-compose exec app php artisan filament:install --panels
docker-compose exec app php artisan make:filament-user
```

### 📍 Akses Poin

| Layanan | URL |
|---|---|
| **URL Dasar API** | `http://recashly.localhost:8888` |
| **Panel Admin** | `http://recashly.localhost:8888/admin` |
| **Adminer (DB GUI)** | `http://adminer.localhost:8888` |
| **Traefik Dashboard** | `http://localhost:8889` |

### 🔌 Endpoint API (Ringkasan)

Dokumentasi lengkap dapat dibuat menggunakan Postman, namun berikut adalah endpoint kuncinya:

- **Autentikasi**: `/api/auth/register`, `/api/auth/login`, `/api/auth/logout`, `/api/auth/me`
- **Reimbursement**: `/api/reimbursements` (GET, POST), `/api/reimbursements/{id}` (GET, PUT)
- **Laporan**: `/api/reports`, `/api/reports/{id}/download`

### 🔧 Perintah Berguna

```bash
# Lihat log container
docker-compose logs -f app

# Jalankan perintah artisan
docker-compose exec app php artisan [command]

# Jalankan migrasi database
docker-compose exec app php artisan migrate

# Hentikan container
docker-compose down
```
