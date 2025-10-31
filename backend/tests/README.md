# PHPUnit Tests

## Overview

Unit tests for core business logic demonstrating testing best practices.

## Test Coverage

**DeviceDetectionServiceTest** - Device, browser, and bot detection (24 test cases)  
**GeoIpServiceTest** - IP geolocation with error handling (10 test cases)

## Running Tests

```bash
# Run all tests (simple output)
docker-compose exec php vendor/bin/phpunit

# Run all tests (human-readable output with test names)
docker-compose exec php vendor/bin/phpunit --testdox

# Run all tests (with colors)
docker-compose exec php vendor/bin/phpunit --testdox --colors=always

# Run specific test file
docker-compose exec php vendor/bin/phpunit tests/Unit/Service/DeviceDetectionServiceTest.php

# Run with debug mode (shows each assertion)
docker-compose exec php vendor/bin/phpunit --debug
```

## Test Structure

```
tests/
├── bootstrap.php
└── Unit/
    └── Service/
        ├── DeviceDetectionServiceTest.php  (24 tests: 7 device + 5 browser + 12 bot)
        └── GeoIpServiceTest.php            (10 tests: 5 private IP + 5 API scenarios)
```

## Notes

- Tests use data providers for comprehensive coverage
- Modern PHP 8 attributes (`#[DataProvider]`) instead of annotations
- Mock HTTP client avoids external API dependencies
- No database required for unit tests
- Fast execution (<20ms total)
