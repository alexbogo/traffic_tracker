<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeoIpService
{
    private const IP_API_URL = 'http://ip-api.com/json/';
    
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    public function getCountryFromIp(string $ip): array
    {
        // Skip for local/private IPs
        if ($this->isPrivateIp($ip)) {
            error_log("GeoIP: Skipping private IP: $ip");
            return ['code' => null, 'name' => null];
        }
        
        try {
            $url = self::IP_API_URL . $ip;
            error_log("GeoIP: Calling $url");
            
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 5,
            ]);
            
            $data = $response->toArray();
            error_log("GeoIP: Response: " . json_encode($data));
            
            if ($data['status'] === 'success') {
                return [
                    'code' => $data['countryCode'] ?? null,
                    'name' => $data['country'] ?? null
                ];
            }
            
            return ['code' => null, 'name' => null];
        } catch (\Exception $e) {
            error_log("GeoIP: Error: " . $e->getMessage());
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
}
