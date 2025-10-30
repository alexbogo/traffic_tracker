# Tracker Embedding Guide

## Overview

The Traffic Tracker provides a standalone JavaScript library that can be embedded in any website to collect visitor analytics data. This guide covers installation, configuration, deployment, and troubleshooting.

## Basic Implementation

### Minimal Setup

Add the tracker script to your HTML pages before the closing `</body>` tag:

```html
<script src="http://localhost:8080/tracker.js"></script>
```

The tracker will automatically initialize and begin tracking page views.

### Automatic Behavior

When loaded, the tracker:
1. Generates a unique browser fingerprint
2. Creates or retrieves a session identifier
3. Captures page metadata (URL, title, referrer)
4. Sends tracking data to the API endpoint
5. Handles errors and retries failed requests

## Configuration

### Available Options

Configure the tracker by defining `window.TRACKER_CONFIG` before loading the script:

```html
<script>
  window.TRACKER_CONFIG = {
    apiUrl: 'https://api.example.com/api/track',
    debug: false,
    autoTrack: true
  };
</script>
<script src="http://localhost:8080/tracker.js"></script>
```

### Configuration Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `apiUrl` | string | `http://localhost:8080/api/track` | API endpoint URL for tracking requests |
| `debug` | boolean | `false` | Enable console logging for debugging |
| `autoTrack` | boolean | `true` | Automatically track page views on load |

### Debug Mode

Enable debug mode to view detailed logging in the browser console:

```html
<script>
  window.TRACKER_CONFIG = { debug: true };
</script>
<script src="http://localhost:8080/tracker.js"></script>
```

Console output includes initialization status, data being sent, request success/failure, and retry attempts.

### Manual Tracking

Disable automatic tracking to control when tracking events occur:

```html
<script>
  window.TRACKER_CONFIG = { autoTrack: false };
</script>
<script src="http://localhost:8080/tracker.js"></script>

<script>
  // Track manually when needed
  window.TrafficTracker.track();
  
  // Example: Track on button click
  document.getElementById('cta-button').addEventListener('click', function() {
    window.TrafficTracker.track();
  });
</script>
```

## Data Collection

### Automatically Collected Data

| Field | Description | Example Value |
|-------|-------------|---------------|
| `url` | Current page URL | `https://example.com/products` |
| `title` | Document title | `Products - Example Store` |
| `fingerprint` | Unique browser identifier | `fp_a7b3c9d2e` |
| `session_id` | Session identifier | `session_1234567890_abc` |
| `user_agent` | Browser user agent string | `Mozilla/5.0 (Windows NT 10.0...)` |
| `referrer` | Previous page URL | `https://google.com/search?q=...` |
| `screen_resolution` | Display dimensions | `1920x1080` |
| `viewport_size` | Browser viewport size | `1280x720` |
| `timestamp` | Visit timestamp (ISO 8601) | `2025-01-15T10:30:45.123Z` |

### Server-Side Enrichment

The backend automatically enriches tracking data with:
- **IP Address:** Hashed using SHA-256 (not stored in plain text)
- **Geographic Location:** Country code and name via IP-API.com
- **Device Type:** Mobile, tablet, or desktop classification
- **Browser:** Browser identification (Chrome, Firefox, Safari, Edge)
- **Bot Detection:** Automated traffic identification

### Privacy Compliance

- IP addresses are one-way hashed before database storage
- Fingerprints use non-reversible hashing algorithms
- No personally identifiable information (PII) is collected or stored
- Geographic data is limited to country-level precision

## Production Deployment

### File Sizes

| File | Uncompressed | Gzipped | Recommended Use |
|------|-------------|---------|-----------------|
| `tracker.js` | ~8 KB | ~3 KB | Development and debugging |
| `tracker.min.js` | ~2 KB | ~1 KB | Production deployment |

### CDN Deployment

For production environments, deploy the tracker to a Content Delivery Network (CDN) for improved performance and reliability.

**Supported CDN Providers:**
- AWS CloudFront
- Cloudflare
- Fastly
- Akamai
- Other CDN providers (KeyCDN, BunnyCDN, etc.)

**Implementation Example:**

```html
<script>
  window.TRACKER_CONFIG = {
    apiUrl: 'https://api.yoursite.com/api/track'
  };
</script>
<script src="https://cdn.yoursite.com/tracker.min.js" async></script>
```

### Loading Strategy

**Asynchronous Loading (Recommended):**

```html
<script src="https://cdn.example.com/tracker.min.js" async></script>
```

Benefits: Non-blocking page load, improved performance

**Deferred Loading:**

```html
<script src="https://cdn.example.com/tracker.min.js" defer></script>
```

Use when script execution order matters.

## Security Configuration

### CORS Setup

Configure Cross-Origin Resource Sharing (CORS) on your API server:

**Symfony Configuration (backend/config/packages/nelmio_cors.yaml):**

```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['https://example.com', 'https://www.example.com']
        allow_methods: ['POST']
        allow_headers: ['Content-Type']
        max_age: 3600
    paths:
        '^/api/track':
            allow_origin: ['*']  # Only if needed for multiple domains
```

### Content Security Policy

If using Content Security Policy (CSP), whitelist the tracker domain:

```html
<meta http-equiv="Content-Security-Policy" 
      content="script-src 'self' https://cdn.example.com;">
```

### Subresource Integrity

For additional security, implement Subresource Integrity (SRI):

```bash
# Generate SRI hash
openssl dgst -sha384 -binary tracker.min.js | openssl base64 -A
```

```html
<script src="https://cdn.example.com/tracker.min.js"
        integrity="sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/ux..."
        crossorigin="anonymous"></script>
```

## Integration Examples

### WordPress Integration

Add to theme's `functions.php`:

```php
function add_traffic_tracker() {
    ?>
    <script>
      window.TRACKER_CONFIG = {
        apiUrl: 'https://api.example.com/api/track'
      };
    </script>
    <script src="https://cdn.example.com/tracker.min.js" async></script>
    <?php
}
add_action('wp_footer', 'add_traffic_tracker');
```

### Single Page Application

Track navigation events in SPAs:

```javascript
// Vue Router
router.afterEach((to, from) => {
    if (window.TrafficTracker) {
        window.TrafficTracker.track();
    }
});

// React Router
import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

function usePageTracking() {
    const location = useLocation();
    
    useEffect(() => {
        if (window.TrafficTracker) {
            window.TrafficTracker.track();
        }
    }, [location]);
}
```

## Testing

### Local Testing

Start the development environment:

```bash
docker-compose up -d
```

Access demo pages:
- http://localhost:8080/demo/page1.html
- http://localhost:8080/demo/page2.html
- http://localhost:8080/demo/page3.html
- http://localhost:8080/demo/page4.html
- http://localhost:8080/demo/page5.html

Verify tracking in database:

```bash
docker-compose exec mysql mysql -utracker_user -ptracker_pass traffic_tracker \
  -e "SELECT * FROM visits ORDER BY visited_at DESC LIMIT 10"
```

### Debug Mode Testing

Enable debug mode to see console output:

```html
<script>
  window.TRACKER_CONFIG = { debug: true };
</script>
<script src="http://localhost:8080/tracker.js"></script>
```

Expected console output:
```
[Traffic Tracker] Initializing tracker...
[Traffic Tracker] Sending tracking data: {...}
[Traffic Tracker] Tracking successful on attempt 1
[Traffic Tracker] Tracker initialized
```

### Production Testing

Test tracking endpoint:

```bash
curl -X POST https://api.example.com/api/track \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/test",
    "title": "Test Page",
    "fingerprint": "test_fp_123"
  }'
```

Expected response: HTTP 204 No Content

## Troubleshooting

### CORS Errors

**Symptom:**
```
Access to fetch at 'https://api.example.com/api/track' from origin 'https://example.com'
has been blocked by CORS policy
```

**Solution:** Configure CORS headers on API server to allow requests from your domain.

### Tracking Not Recording

**Diagnostic Steps:**

1. Enable debug mode to view console logs
2. Check network tab in browser DevTools for failed requests
3. Verify API endpoint is accessible via curl
4. Disable ad blockers that may block tracking scripts
5. Check server logs for error messages

### Ad Blocker Interference

Some ad blockers may block tracking scripts.

**Mitigation:**
- Rename tracker file to non-tracking name (e.g., `analytics.js`)
- Host on same domain as main site
- Implement server-side tracking as fallback

### Script Loading Failures

**Check:**
- CDN availability and uptime
- Correct file path and permissions
- Network connectivity
- Browser console for loading errors

## API Reference

### window.TrafficTracker.track()

Manually trigger a page view tracking event.

**Syntax:**
```javascript
window.TrafficTracker.track();
```

**Returns:** Promise<void>

**Example:**
```javascript
document.getElementById('submit-form').addEventListener('submit', function(e) {
    e.preventDefault();
    window.TrafficTracker.track();
    // Submit form
});
```

### window.TrafficTracker.config

Access current tracker configuration.

**Syntax:**
```javascript
const config = window.TrafficTracker.config;
```

**Returns:** Object with current configuration

**Example:**
```javascript
console.log(window.TrafficTracker.config.apiUrl);
// Output: "https://api.example.com/api/track"

console.log(window.TrafficTracker.config.debug);
// Output: false
```

## Browser Compatibility

### Supported Browsers

| Browser | Minimum Version |
|---------|----------------|
| Chrome | 60+ |
| Firefox | 55+ |
| Safari | 11+ |
| Edge | 79+ |
| Opera | 47+ |

### Fallback Behavior

For browsers that don't support modern APIs:
- Fingerprinting falls back to random identifier generation
- SessionStorage unavailable: Generates new session ID per page load

## Performance

### Best Practices

1. Use minified version in production (`tracker.min.js`)
2. Load asynchronously with `async` attribute
3. Implement long cache headers (1 year recommended)
4. Use CDN for global distribution
5. Enable gzip or brotli compression

### Cache Configuration

Recommended cache headers:

```
Cache-Control: public, max-age=31536000, immutable
Content-Type: application/javascript
Content-Encoding: gzip
```

### Performance Metrics

- Script load time: < 100ms (with CDN)
- Execution time: < 50ms
- Network request: < 200ms (tracking POST)
- Total overhead: < 350ms

## Deployment Checklist

Before deploying to production:

- [ ] Minify tracker.js to tracker.min.js
- [ ] Upload to CDN or static hosting
- [ ] Configure cache headers (1 year TTL)
- [ ] Enable HTTPS on CDN
- [ ] Update apiUrl in configuration
- [ ] Configure CORS on API server
- [ ] Test from production domain
- [ ] Verify database is recording visits
- [ ] Set up monitoring and alerts

## Conclusion

The Traffic Tracker provides a lightweight, privacy-focused solution for website analytics. Follow this guide for proper implementation, security configuration, and production deployment.
