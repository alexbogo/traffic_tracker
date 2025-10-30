# Architecture Documentation

## Overview

This document outlines the architectural decisions and design patterns used in the Traffic Tracker application.

## Design Patterns

### Repository Pattern

**Status:** Implemented

All database operations are encapsulated in repository classes, providing clean separation between business logic and data persistence.

**Example:**
```php
// VisitRepository::create()
public function create(
    \App\Entity\Page $page,
    string $ipAddressHash,
    string $fingerprint,
    bool $isUnique,
    ?string $userAgent = null,
    ?string $referrer = null,
    ?string $countryCode = null,
    ?string $countryName = null,
    bool $isBot = false,
    ?string $deviceType = null,
    ?string $browser = null
): Visit {
    $visit = new Visit();
    $visit->setPage($page);
    $visit->setIpAddressHash($ipAddressHash);
    $visit->setVisitorFingerprint($fingerprint);
    $visit->setIsUnique($isUnique);
    $visit->setUserAgent($userAgent);
    $visit->setReferrer($referrer);
    $visit->setIpCountryCode($countryCode);
    $visit->setIpCountryName($countryName);
    $visit->setIsBot($isBot);
    $visit->setDeviceType($deviceType);
    $visit->setBrowser($browser);
    
    $this->save($visit);
    
    return $visit;
}

public function save(Visit $visit, bool $flush = true): void
{
    $this->getEntityManager()->persist($visit);
    
    if ($flush) {
        $this->getEntityManager()->flush();
    }
}
```

**Benefits:**
- Testability through mocking
- Centralized data access logic
- Flexibility to change storage mechanisms

### Service Layer

**Status:** Implemented

Services orchestrate business logic and coordinate between repositories and external services.

**Example:**
```php
// VisitService orchestrates the visit creation workflow
public function createVisit(array $data, string $ipAddress, string $userAgent): Visit
{
    // Find or create page
    $page = $this->pageRepository->findOrCreate(
        $data['url'], 
        $data['title'] ?? 'Untitled'
    );
    
    // Generate fingerprint
    $fingerprint = $this->generateFingerprint($ipAddress, $userAgent);
    
    // Check uniqueness
    $isUnique = !$this->visitRepository->hasRecentVisit($page, $fingerprint);
    
    // Get enrichment data
    $countryData = $this->geoIpService->getCountryFromIp($ipAddress);
    $isBot = $this->deviceDetectionService->isBot($userAgent);
    $deviceType = $this->deviceDetectionService->detectDevice($userAgent);
    $browser = $this->deviceDetectionService->detectBrowser($userAgent);
    
    // Create and persist via repository
    return $this->visitRepository->create(
        page: $page,
        ipAddressHash: $this->hashIp($ipAddress),
        fingerprint: $fingerprint,
        isUnique: $isUnique,
        userAgent: $userAgent,
        referrer: $data['referrer'] ?? null,
        countryCode: $countryData['code'] ?? null,
        countryName: $countryData['name'] ?? null,
        isBot: $isBot,
        deviceType: $deviceType,
        browser: $browser
    );
}
```

**Benefits:**
- Controllers remain thin
- Business logic is reusable
- Easy to test with mocked dependencies

### Data Transfer Objects (DTOs)

**Status:** Not Implemented

DTOs are intentionally not used. Data is passed as arrays and Symfony entities.

**Rationale:**
- Project scope does not justify the added complexity
- Entities map cleanly to API responses
- Symfony validation component handles input validation
- Can be added later if API versioning or complex transformations become necessary

### Dependency Injection

**Status:** Implemented throughout

Constructor injection is used with Symfony's autowiring feature.

**Example:**
```php
public function __construct(
    private PageRepository $pageRepository,
    private VisitRepository $visitRepository,
    private GeoIpService $geoIpService,
    private DeviceDetectionService $deviceDetectionService
) {}
```

## Code Organization

### Layered Architecture

```
Controllers (HTTP Layer)
    ↓
Services (Business Logic)
    ↓
Repositories (Data Access) + External Services
```

**Controllers:**
- Validate input
- Call services
- Return HTTP responses
- No business logic

**Services:**
- Implement business rules
- Coordinate repositories
- Call external APIs
- Handle complex workflows

**Repositories:**
- Create and persist entities
- Execute custom queries
- No business logic

**Entities:**
- Represent database tables
- Define relationships
- Getters and setters only

## External Service Integration

### GeoIpService

Uses IP-API.com for country-level geolocation.

```php
public function getCountryFromIp(string $ip): array
{
    // Skip for local/private IPs
    if ($this->isPrivateIp($ip)) {
        return ['code' => null, 'name' => null];
    }
    
    try {
        $response = $this->httpClient->request('GET', self::IP_API_URL . $ip, [
            'timeout' => 5,
        ]);
        
        $data = $response->toArray();
        
        if ($data['status'] === 'success') {
            return [
                'code' => $data['countryCode'] ?? null,
                'name' => $data['country'] ?? null
            ];
        }
        
        return ['code' => null, 'name' => null];
    } catch (\Exception $e) {
        return ['code' => null, 'name' => null];
    }
}

private function isPrivateIp(string $ip): bool
{
    return !filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
}
```

**Characteristics:**
- Graceful failure handling
- Returns null for private IPs
- No signup or API key required
- Rate limit: 45 requests/minute (free tier)

### DeviceDetectionService

Custom regex-based pattern matching for device and bot detection.

```php
public function detectDevice(string $userAgent): string
{
    if (preg_match('/mobile|android|iphone/i', $userAgent)) {
        return 'mobile';
    }
    if (preg_match('/tablet|ipad/i', $userAgent)) {
        return 'tablet';
    }
    return 'desktop';
}

public function detectBrowser(string $userAgent): string
{
    if (preg_match('/chrome/i', $userAgent)) return 'Chrome';
    if (preg_match('/firefox/i', $userAgent)) return 'Firefox';
    if (preg_match('/safari/i', $userAgent)) return 'Safari';
    if (preg_match('/edge/i', $userAgent)) return 'Edge';
    return 'Unknown';
}

public function isBot(string $userAgent): bool
{
    $botPatterns = [
        'bot', 'crawler', 'spider', 'scraper', 
        'headless', 'curl', 'wget', 'python'
    ];
    
    foreach ($botPatterns as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}
```

**Characteristics:**
- No external dependencies
- Fast pattern matching
- Easily extensible

## Database Design

### Tables

- **users:** Authentication credentials
- **pages:** Tracked web pages  
- **visits:** Visit records with enrichment data

### Indexing Strategy

Indexes optimize common query patterns:

**visits table:**
- `idx_page_visited` (page_id, visited_at): Analytics queries by date
- `idx_fingerprint` (visitor_fingerprint): Duplicate detection
- `idx_visited_at` (visited_at): Date range filtering
- `idx_country` (ip_country_code): Geographic analytics
- `idx_bot` (is_bot): Bot filtering
- `idx_session` (session_id): Session-based queries
- `idx_unique` (is_unique): Unique visitor queries
- `idx_device` (device_type): Device analytics
- `idx_browser` (browser): Browser analytics

### Schema Initialization

The complete database schema is created via `docker/mysql/init.sql` on first container startup. The schema includes all tables, indexes, and constraints needed for the application.

## Security

### Authentication

JWT authentication via Lexik JWT Authentication Bundle:
- Stateless token-based auth
- Public endpoints: `/api/login`, `/api/track`
- Protected endpoints: All other `/api/*` routes

### Data Privacy

- IP addresses hashed with SHA-256
- Visitor fingerprints are one-way hashes
- No plain-text PII stored
- Country-level geolocation only

### Input Validation

- Symfony validation component
- Type declarations enforce data types
- Doctrine parameter binding prevents SQL injection

## Performance Optimization

### Database
- Strategic indexing based on query patterns
- Efficient Doctrine Query Builder usage
- Connection pooling via Doctrine DBAL

### API
- Tracking endpoint returns 204 No Content (minimal payload)
- JWT tokens cached in client localStorage
- CORS preflight responses cached

### Frontend
- Minified JavaScript tracker (2KB)
- Async script loading
- Vite build optimization

## Scalability Considerations

### When to Scale

Consider architectural changes when:
- Traffic exceeds single server capacity
- Database becomes bottleneck
- Geographic distribution required

### Potential Solutions

- Horizontal scaling with load balancer
- Database read replicas
- Redis for caching and sessions
- Message queue for async processing
- Database sharding by date range

## Conclusion

The architecture prioritizes simplicity and maintainability for the current scope. The layered design with clear separation of concerns provides a foundation that can evolve as requirements grow.

**Core Principles:**
- Separation of concerns
- Dependency injection
- Single responsibility
- Explicit dependencies
- Fail-safe external integrations
- Privacy by design
