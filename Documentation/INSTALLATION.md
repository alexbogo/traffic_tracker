# Installation Guide

Complete setup instructions for Traffic Tracker from a fresh Git clone.

## Prerequisites

- Docker Engine 20.10+ and Docker Compose 2.0+
- Git
- Available ports: 8080, 3307, 5173

## Installation Steps

```bash
# Clone repository
git clone <repository-url>
cd traffic_tracker

# Start all services
docker-compose up -d --build

# Monitor initialization (30-60 seconds)
docker-compose logs -f php
```

**Access Points:**
- Frontend Dashboard: http://localhost:8080 (login: admin / admin123)
- Demo Pages: http://localhost:8080/demo/page1.html
- API Base: http://localhost:8080/api

**Note:** Default admin user (admin/admin123) is created automatically via database initialization script.

## Automated Setup Process

All dependencies and configuration are handled automatically on first startup.

**PHP Container:**
- Installs Composer dependencies (vendor/)
- Creates var/cache/ and var/log/ directories
- Executes database migrations
- Generates JWT keys
- Sets file permissions

**Node Container:**
- Installs NPM dependencies (node_modules/)
- Starts Vite development server on port 5173

**MySQL Container:**
- Creates database and user
- Executes schema from docker/mysql/init.sql

No manual intervention required beyond running docker-compose.

## Verification

```bash
# Check container status
docker-compose ps

# Test authentication endpoint
curl http://localhost:8080/api/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Expected response: JWT token in JSON format
```

## Troubleshooting

**Container startup failures:**
```bash
docker-compose logs php
docker-compose logs node
docker-compose logs mysql
docker-compose down && docker-compose up -d --build
```

**Port conflicts:**
Edit docker-compose.yml port mappings if 8080, 3307, or 5173 are unavailable.

**Permission errors:**
```bash
docker-compose exec php chmod -R 775 var/cache var/log
```

## Development Commands

```bash
# Service management
docker-compose up -d                    # Start services
docker-compose down                     # Stop services
docker-compose restart <service>        # Restart specific service
docker-compose logs -f <service>        # View logs

# Container access
docker-compose exec php sh              # PHP container shell
docker-compose exec node sh             # Node container shell
docker-compose exec mysql mysql -utracker_user -ptracker_pass traffic_tracker

# Symfony console
docker-compose exec php bin/console <command>
docker-compose exec php bin/console debug:router
docker-compose exec php bin/console cache:clear

# Create additional users (admin user already exists)
docker-compose exec php bin/console app:create-user <username> <email> <password>
```

## Notes

- First startup duration: 30-60 seconds (dependency installation)
- Subsequent startups: 5-10 seconds (dependencies cached)
- Database data persists in Docker volume mysql_data
- Node modules persist in Docker volume node_modules
- JWT keys auto-generated on first run (backend/config/jwt/)

For architecture details and API documentation, refer to README.md and Documentation/ARCHITECTURE.md.
