# 🌐 Recashly Backend API

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

> **Centralized API & Admin Platform for [Recashly Mobile App](https://github.com/adydhermawan/Reimburse).**  
> Handles data synchronization, image processing, and automated reporting.
>
> 🚀 **Flexible Deployment**: Supports **Docker**, **VPS**, or **Zero-Cost Serverless Stack** (Vercel, TiDB, Cloudinary).

---

## 🏗 System Architecture

The backend serves as the single source of truth for the Recashly ecosystem. It is designed to support the **Offline-First** mobile app through a synchronized data model.

### Key Capabilities

- **🔄 Centralized Sync Engine**: Manages delta syncs with mobile devices, handling conflict resolution and data integrity.
- **📄 PDF Generation Engine**: Automatically compiles monthly expenses into professional PDF reports for Finance teams.
- **🤖 Client Auto-Registration**: Smart logic to validate and register new clients created from mobile field inputs.
- **☁️ Cloudinary Integration**: Offloads image storage and transformation, keeping the core API stateless and fast.

---

## 🛠 Tech Stack

| Component | Technology | Description |
|---|---|---|
| **Framework** | **Laravel 11** | Robust PHP framework for API & Logic. |
| **Admin Panel** | **FilamentPHP v3** | Beautiful TALL-stack admin dashboard. |
| **Database** | **MySQL 8.0** | Compatible with TiDB Serverless. |
| **Storage** | **Cloudinary** | Image hosting & optimization. |
| **Deploy** | **Vercel** | Serverless function deployment. |

---

## 🗄️ Database Schema (Abstract)

A simplified view of the core relationships:

- **Users**: Field Agents & Finance Admins.
- **Clients**: Companies visited by agents.
  - *Has Many* -> **Projects**
- **Reimbursements**: The core transaction record.
  - *Belongs To* -> **User**, **Client**, **Category**
  - *Has One* -> **ReceiptImage** (URL & Metadata)
- **Reports**: monthly/weekly aggregations.
  - *Has Many* -> **Reimbursements**

---

## 🚀 Deployment Options

The system is designed to be infrastructure-agnostic. You can deploy it using:

1.  **Docker / VPS**: Standard deployment using the provided `docker-compose.yml` or manual setup on Ubuntu/Debian.
2.  **Serverless (Zero-Cost)**: Configuration for Vercel + TiDB + Cloudinary.

### Zero-Cost Stack Setup

This project is configured for a **Free Tier** production environment:

1. **Vercel**: Hosts the Laravel application as Serverless Functions.
2. **TiDB Cloud**: Provides a serverless MySQL-compatible database.
3. **Cloudinary**: Handles file storage (since Vercel is ephemeral).

### Environment Setup (`.env`)

```env
APP_KEY=base64:...
DB_CONNECTION=mysql
DB_HOST=gateway01.us-west-2.prod.aws.tidbcloud.com
DB_USERNAME=...
CLOUDINARY_URL=...
FILESYSTEM_DISK=cloudinary
```

---

## 🔌 API Overview

Full documentation is available via [Postman Collection](./postman).

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/auth/login` | Sanctum Token Issue |
| GET | `/api/sync/pull` | Download changes (WatermelonDB format) |
| POST | `/api/sync/push` | Upload offline changes |
| POST | `/api/reimbursements` | Create single entry (Online mode) |

---

## 📦 Running Locally (Docker)

```bash
# 1. Start Containers
docker-compose up -d

# 2. Run Migrations & Seed
docker-compose exec app php artisan migrate --seed

# 3. Access Admin Panel
# http://localhost:8000/admin
# User: admin@recashly.com | Pass: password
```

---

## 📄 License

MIT License.
