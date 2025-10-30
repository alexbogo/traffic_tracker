/**
 * Traffic Tracker - Embeddable JavaScript Tracker
 * 
 * Usage:
 * <script src="https://your-domain.com/tracker.js"></script>
 * 
 * Or for self-hosted:
 * <script src="http://localhost:8080/tracker.js"></script>
 * 
 * Optional configuration (must be defined BEFORE loading tracker.js):
 * <script>
 *   window.TRACKER_CONFIG = {
 *     apiUrl: 'https://your-api.com/api/track',
 *     debug: false,
 *     autoTrack: true
 *   };
 * </script>
 */

(function() {
    'use strict';

    // Configuration with defaults
    const config = window.TRACKER_CONFIG || {};
    const API_URL = config.apiUrl || 'http://localhost:8080/api/track';
    const DEBUG = config.debug || false;
    const AUTO_TRACK = config.autoTrack !== false; // Default true

    // Logging helper
    function log(message, data) {
        if (DEBUG) {
            console.log('[Traffic Tracker]', message, data || '');
        }
    }

    // Error handler
    function logError(message, error) {
        if (DEBUG) {
            console.error('[Traffic Tracker]', message, error);
        }
    }

    /**
     * Generate browser fingerprint
     * Creates a consistent hash from browser characteristics
     */
    function generateFingerprint() {
        try {
            const components = [
                navigator.userAgent,
                navigator.language || navigator.userLanguage,
                screen.width + 'x' + screen.height,
                screen.colorDepth,
                new Date().getTimezoneOffset(),
                !!window.sessionStorage,
                !!window.localStorage,
                !!window.indexedDB,
                typeof window.openDatabase,
                navigator.cpuClass || 'unknown',
                navigator.platform,
                navigator.doNotTrack || 'unknown'
            ];

            const dataString = components.join('|');
            
            // Simple hash function (FNV-1a)
            let hash = 2166136261;
            for (let i = 0; i < dataString.length; i++) {
                hash ^= dataString.charCodeAt(i);
                hash += (hash << 1) + (hash << 4) + (hash << 7) + (hash << 8) + (hash << 24);
            }
            
            return 'fp_' + (hash >>> 0).toString(36);
        } catch (error) {
            logError('Error generating fingerprint:', error);
            return 'fp_' + Math.random().toString(36).substr(2, 9);
        }
    }

    /**
     * Get or create session ID
     * Stored in sessionStorage for consistency across page views
     */
    function getSessionId() {
        try {
            let sessionId = sessionStorage.getItem('tracker_session_id');
            if (!sessionId) {
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                sessionStorage.setItem('tracker_session_id', sessionId);
            }
            return sessionId;
        } catch (error) {
            // Fallback if sessionStorage not available
            return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
    }

    /**
     * Capture all visit data
     */
    function captureVisitData() {
        return {
            url: window.location.href,
            title: document.title,
            fingerprint: generateFingerprint(),
            session_id: getSessionId(),
            user_agent: navigator.userAgent,
            referrer: document.referrer || null,
            screen_resolution: screen.width + 'x' + screen.height,
            viewport_size: window.innerWidth + 'x' + window.innerHeight,
            timestamp: new Date().toISOString()
        };
    }

    /**
     * Send tracking data to server
     * Includes retry logic for reliability
     */
    async function sendToServer(data, retries = 3) {
        log('Sending tracking data:', data);

        for (let attempt = 1; attempt <= retries; attempt++) {
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                    // Don't send credentials for cross-origin requests
                    credentials: 'omit'
                });

                if (response.ok) {
                    log('Tracking successful on attempt', attempt);
                    return true;
                }

                if (response.status >= 400 && response.status < 500) {
                    // Client error, don't retry
                    logError('Client error, not retrying:', response.status);
                    return false;
                }

                // Server error, retry
                log('Server error, retrying...', {
                    attempt: attempt,
                    status: response.status
                });
            } catch (error) {
                logError('Attempt ' + attempt + ' failed:', error);
                
                if (attempt === retries) {
                    logError('All retry attempts failed');
                    return false;
                }

                // Wait before retry (exponential backoff)
                await new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
            }
        }

        return false;
    }

    /**
     * Track page view
     * Main tracking function - call this to track a visit
     */
    async function trackPageView() {
        try {
            const data = captureVisitData();
            await sendToServer(data);
        } catch (error) {
            logError('Error tracking page view:', error);
        }
    }

    /**
     * Track custom event
     * For future expansion - allows tracking custom events
     */
    function trackEvent(eventName, eventData = {}) {
        log('Custom event tracking not yet implemented:', { eventName, eventData });
        // Future implementation
    }

    /**
     * Initialize tracker
     */
    function init() {
        log('Initializing tracker...');
        
        // Auto-track page view on load
        if (AUTO_TRACK) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', trackPageView);
            } else {
                // Document already loaded
                trackPageView();
            }
        }

        log('Tracker initialized');
    }

    // Expose public API
    window.TrafficTracker = {
        track: trackPageView,
        trackEvent: trackEvent,
        config: {
            apiUrl: API_URL,
            debug: DEBUG
        }
    };

    // Auto-initialize
    init();
})();
