# Device Incident Management API

## Overview

This project is a REST API built with Laravel 12 and SQL Server for managing:

- Device assignments
- Technical incident tickets
- Activity logs
- Incident history

The project includes:

- Laravel Sanctum authentication
- Rate limiting
- Discord Webhook alerts
- Sentry integration
- Docker support
- Structured JSON responses
- Clean architecture using Services and Requests

---

# Architecture

## Layers

- Controllers
- Services
- Requests
- Middleware
- Models
- Migrations
- Seeders

Business logic is implemented inside Service classes.

---

# Technologies

- PHP 8.3
- Laravel 12
- SQL Server
- Docker
- Laravel Sanctum
- Sentry
- Discord Webhooks

---

# Installation

## Clone repository

```bash
git clone <repository-url>
cd laravel_ticket_api
```

## Environment configuration

Copy:

```bash
cp .env.example .env
```

Update:

```env
APP_NAME=DeviceIncidentAPI
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlsrv
DB_HOST=sqlserver
DB_PORT=1433
DB_DATABASE=ticketdb
DB_USERNAME=sa
DB_PASSWORD=YourStrongPassword123

DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/your-webhook

SENTRY_LARAVEL_DSN=https://your-sentry-dsn
```

---

# Docker
## Create a container with docker
```
docker run -d \
--name sqlserver2022 \
-e ACCEPT_EULA=Y \
-e MSSQL_SA_PASSWORD="MiClaveSegura2026!" \
-p 1433:1433 \
-v sql_data:/var/opt/mssql \
mcr.microsoft.com/mssql/server
```
## Start containers

```bash
docker compose up -d --build
```

## Run migrations

```bash
docker compose exec app php artisan migrate --seed
```

## Generate app key

```bash
docker compose exec app php artisan key:generate
```

---

# Authentication

The project uses Laravel Sanctum.

## Register

POST /api/register

## Login

POST /api/login

Use the returned Bearer Token.

---

# API Endpoints

| Method | Endpoint |
|---|---|
| POST | /api/register |
| POST | /api/login |
| GET | /api/tickets |
| GET | /api/tickets/{id} |
| POST | /api/tickets |
| PUT | /api/tickets/{id} |
| DELETE | /api/tickets/{id} |
| POST | /api/devices/assign |
| GET | /api/devices |

---

# Rate Limiting

All API endpoints are protected using Laravel Rate Limiter.

Limit:
- 10 requests per minute per IP

If exceeded:
- HTTP 429 returned
- Discord notification triggered

---

# Discord Alerts

Automatic Discord alerts are sent for:

- HTTP 500 errors
- Rate limit violations
- Critical failures

Payload includes:
- endpoint
- HTTP method
- IP
- timestamp
- exception message

---

# Sentry Monitoring

Sentry captures:

- Unhandled exceptions
- HTTP 500 errors
- Runtime failures

Install:

```bash
composer require sentry/sentry-laravel
```

---

# Postman Flow

## Authentication Flow

1. Register user
2. Login
3. Copy Bearer Token
4. Use Authorization Header

## Ticket Flow

1. Create ticket
2. List tickets
3. Update status
4. Delete ticket

## Device Assignment Flow

1. Create devices
2. Assign device
3. Check assignment logs

---

# Database Relations

- User has many Tickets
- Ticket belongs to User
- Device has many Assignments
- Assignment belongs to Device
- Activity Logs store API actions

---

# Evidence Required

## Discord

Trigger:
- invalid request
- rate limit overflow
- forced exception

## Sentry

Force:
```php
throw new Exception('Test Sentry');
```

---

# Project Structure

```text
app/
 ├── Http/
 ├── Models/
 ├── Services/
 ├── Middleware/
 ├── Requests/
 └── Exceptions/

database/
 ├── migrations/
 └── seeders/
```
