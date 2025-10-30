<?php

namespace App\Service;

use App\Entity\Visit;
use App\Repository\PageRepository;
use App\Repository\VisitRepository;

class VisitService
{
    public function __construct(
        private PageRepository $pageRepository,
        private VisitRepository $visitRepository,
        private GeoIpService $geoIpService,
        private DeviceDetectionService $deviceDetectionService
    ) {}

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

    private function hashIp(string $ip): string
    {
        return hash('sha256', $ip);
    }

    private function generateFingerprint(string $ip, string $userAgent): string
    {
        return hash('sha256', $ip . $userAgent);
    }
}
