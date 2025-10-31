# Data Flow Diagram - Traffic Tracker

```
┌─────────────────────────────────────────────────────────────────┐
│                    TRAFFIC TRACKER DATA FLOW                     │
└─────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │   Website    │
    │   Visitor    │
    └──────┬───────┘
           │
           │ 1. Opens page with tracker
           │
           ▼
    ┌──────────────────────┐
    │  tracker.js          │
    │  (Vanilla JS)        │
    │                      │
    │  • Capture URL       │
    │  • Generate          │
    │    fingerprint       │
    │  • Get session ID    │
    │  • Screen size       │
    │  • Referrer          │
    └──────┬───────────────┘
           │
           │ 2. POST /api/track
           │    {url, title, fingerprint, ...}
           │
           ▼
    ┌──────────────────────┐
    │  TrackingController  │
    │  (Symfony)           │
    └──────┬───────────────┘
           │
           │ 3. Forward to service
           │
           ▼
    ┌──────────────────────┐         ┌────────────────┐
    │   VisitService       │────────▶│  GeoIpService  │
    │                      │         │  IP-API.com    │
    │  Orchestrates:       │◀────────│  (Country)     │
    │  • Page lookup       │         └────────────────┘
    │  • Fingerprint       │
    │  • Enrichment        │         ┌────────────────┐
    │  • Uniqueness check  │────────▶│ DeviceDetect   │
    │  • Visit creation    │         │ Service        │
    │                      │◀────────│ (Device/Bot)   │
    └──────┬───────────────┘         └────────────────┘
           │
           │ 4. Create/Update records
           │
           ▼
    ┌──────────────────────┐
    │   MySQL Database     │
    │                      │
    │  ┌────────────────┐  │
    │  │ users          │  │
    │  └────────────────┘  │
    │  ┌────────────────┐  │
    │  │ pages          │  │
    │  └────────────────┘  │
    │  ┌────────────────┐  │
    │  │ visits         │  │
    │  │ • fingerprint  │  │
    │  │ • country      │  │
    │  │ • device       │  │
    │  │ • browser      │  │
    │  │ • is_bot       │  │
    │  │ • is_unique    │  │
    │  └────────────────┘  │
    └──────▲───────────────┘
           │
           │ 5. Query analytics
           │
    ┌──────┴───────────────┐
    │ DashboardController  │
    │ + StatsAggregator    │
    │                      │
    │  • Unique visitors   │
    │  • Total visits      │
    │  • Country breakdown │
    │  • Time series       │
    └──────┬───────────────┘
           │
           │ 6. JSON response
           │
           ▼
    ┌──────────────────────┐
    │   Vue.js Dashboard   │
    │                      │
    │  • Login (JWT)       │
    │  • Page selector     │
    │  • Date filter       │
    │  • Charts            │
    │  • Tables            │
    └──────────────────────┘
```

## Key Components

**Client Side:**
- tracker.js: Captures visit data, generates fingerprint

**Backend:**
- TrackingController: Receives POST requests
- VisitService: Orchestrates visit creation
- GeoIpService: Country detection via IP-API.com
- DeviceDetectionService: Device, browser, bot detection
- DashboardController: Analytics API endpoints
- StatsAggregator: Calculates statistics

**Database:**
- users: Authentication
- pages: Tracked URLs
- visits: Enriched visit records

**Frontend:**
- Vue.js SPA with JWT authentication
- Charts, filters, analytics display
