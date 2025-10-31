# Website Traffic Tracker

A web analytics platform for tracking website visits with unique visitor identification, geolocation, and bot detection. Built with Symfony 7.3, Vue.js 3, and MySQL.

## Quick Start

```bash
git clone <repository-url>
cd traffic_tracker
docker-compose up -d --build
```

Access dashboard at http://localhost:5173 (login: admin/admin123) after 30-60 seconds.

## Features

- JavaScript tracker for client-side data collection
- Unique visitor identification via browser fingerprinting
- Real-time visit tracking with Vue.js dashboard
- IP-based geolocation (country-level via IP-API.com)
- Automated bot detection and device/browser identification
- JWT-based authentication
- Docker containerized deployment
- Privacy-compliant (IP hashing, no PII storage)

## Technology Stack

**Backend:** Symfony 7.3 (PHP 8.2), Doctrine ORM, JWT Authentication, MySQL 8.0  
**Frontend:** Vue.js 3, Vite 7, Vue Router 4, Bootstrap 5, Chart.js  
**Infrastructure:** Docker, Nginx, Node.js 20  
**Testing:** PHPUnit 11 ([backend/tests/README.md](backend/tests/README.md))

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Ports 8080, 5173, and 3307 available

## Installation

```bash
git clone <repository-url>
cd traffic_tracker
docker-compose up -d --build
```

Wait 30-60 seconds for automatic dependency installation.

**Access:**
- Dashboard: http://localhost:5173 (login: admin/admin123)
- Demo Pages: http://localhost:8080/demo/page1.html
- API: http://localhost:8080/api

**Create additional users:**
```bash
docker-compose exec php bin/console app:create-user <username> <email> <password>
```

## Embedding the Tracker

### Basic Usage

```html
<script src="http://localhost:8080/tracker.js"></script>
```

### With Configuration

```html
<script>
  window.TRACKER_CONFIG = {
    apiUrl: 'https://api.yourdomain.com/api/track',
    debug: false,
    autoTrack: true
  };
</script>
<script src="https://cdn.yourdomain.com/tracker.min.js" async></script>
```

## API Endpoints

### Authentication

```http
POST /api/login
Content-Type: application/json

{"username": "admin", "password": "admin123"}
```

### Tracking (Public)

```http
POST /api/track
Content-Type: application/json

{
  "url": "https://example.com/page",
  "title": "Page Title",
  "fingerprint": "fp_a7b3c9d2e",
  "user_agent": "Mozilla/5.0...",
  "referrer": "https://google.com",
  "screen_resolution": "1920x1080",
  "session_id": "session_123"
}
```

Returns `204 No Content`. Server enriches with IP geolocation, device type, browser, and bot detection.

### Analytics (Protected)

Requires JWT token in `Authorization: Bearer <token>` header.

```http
GET /api/pages
GET /api/pages/{id}/stats?start_date=2025-01-01&end_date=2025-01-31&exclude_bots=1
GET /api/pages/{id}/visits?page=1&limit=50
GET /api/me
```

## Database Schema

**users:** Authentication (id, username, email, password, roles)  
**pages:** Tracked pages (id, url, title, timestamps)  
**visits:** Visit records (id, page_id, fingerprint, ip_hash, country, device_type, browser, is_bot, is_unique, timestamps)

Full schema: `docker/mysql/init.sql`

## Testing

```bash
# Run all tests
docker-compose exec php vendor/bin/phpunit

# Human-readable output with test names
docker-compose exec php vendor/bin/phpunit --testdox

# With colors
docker-compose exec php vendor/bin/phpunit --testdox --colors=always

# Run specific test
docker-compose exec php vendor/bin/phpunit tests/Unit/Service/DeviceDetectionServiceTest.php
```

**Test Suite:** 34 tests (24 device/browser/bot detection + 10 IP geolocation)  
See [backend/tests/README.md](backend/tests/README.md) for details.

## Development

### Common Commands

```bash
# Access containers
docker-compose exec php sh
docker-compose exec mysql mysql -utracker_user -ptracker_pass traffic_tracker
docker-compose exec node sh

# Symfony console
docker-compose exec php bin/console debug:router
docker-compose exec php bin/console cache:clear

# View logs
docker-compose logs -f php
docker-compose logs -f nginx
```

### Database Connection

```
Host:     localhost
Port:     3307
Database: traffic_tracker
Username: tracker_user
Password: tracker_pass
```

### Frontend Development

```bash
docker-compose exec node sh
npm run dev    # http://localhost:5173 with HMR
npm run build
```

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=prod` and generate strong `APP_SECRET`
- [ ] Configure production database credentials
- [ ] Update JWT keys with strong passphrase
- [ ] Configure CORS for production domains
- [ ] Deploy `tracker.min.js` to CDN
- [ ] Enable HTTPS for all endpoints
- [ ] Set up monitoring and logging
- [ ] Configure database backups

### Tracker Deployment

```bash
npx terser tracker.js -o tracker.min.js --compress --mangle
```

Upload to CDN and update embed code.

## Troubleshooting

### Containers Won't Start

```bash
docker-compose logs <service-name>
docker-compose down && docker-compose up -d --build
```

### Tracking Not Working

1. Enable debug mode: `window.TRACKER_CONFIG = { debug: true }`
2. Check CORS configuration
3. Verify endpoint: `curl -X POST http://localhost:8080/api/track -H "Content-Type: application/json" -d '{"url":"test","title":"test","fingerprint":"test"}'`

### Geolocation Not Working

IP-API.com does not work for localhost/private IPs. Test with public IP or insert test data:

```sql
UPDATE visits SET ip_country_code = 'US', ip_country_name = 'United States' WHERE id = 1;
```

## Project Structure

```
traffic_tracker/
├── backend/              # Symfony API
│   ├── config/          # Configuration files
│   └── src/             # Controllers, Services, Entities, Repositories
├── frontend/            # Vue.js dashboard
│   ├── public/          # tracker.js, tracker.min.js
│   └── src/             # Components, views, services
├── demo/                # Demo tracking pages
├── docker/              # Docker configuration
│   ├── mysql/init.sql  # Database schema
│   ├── nginx/          # Nginx config
│   └── php/            # PHP Dockerfile
├── Documentation/       # Technical documentation
│   ├── ARCHITECTURE.md
│   └── TRACKER-EMBEDDING.md
└── docker-compose.yml   # Container orchestration
``` 

## Documentation

- **[ARCHITECTURE.md](Documentation/ARCHITECTURE.md)** - System architecture and design patterns
- **[INSTALLATION.md](Documentation/INSTALLATION.md)** - Complete installation and troubleshooting
- **[TRACKER-EMBEDDING.md](Documentation/TRACKER-EMBEDDING.md)** - Tracker embedding and CDN deployment
- **[DATA_FLOW.md](Documentation/DATA_FLOW.md)** - Application data flow
- **[Tests README](backend/tests/README.md)** - PHPUnit test suite documentation

## Technical Notes

**Geolocation:** IP-API.com (45 req/min free tier, no signup)  
**Security:** SHA-256 IP hashing, JWT auth, CORS protection  
**Privacy:** No PII storage, country-level geolocation only  
**Performance:** 2KB minified tracker, database indexing, 204 No Content responses

---

**Version:** 1.0.0  
**Status:** Production Ready
