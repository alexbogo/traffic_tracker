<?php

namespace App\Controller;

use App\Service\GeoIpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class DebugController extends AbstractController
{
    #[Route('/debug/geoip', name: 'debug_geoip', methods: ['GET'])]
    public function debugGeoip(Request $request, GeoIpService $geoIpService): JsonResponse
    {
        $ip = $request->query->get('ip', '8.8.8.8');
        
        $result = $geoIpService->getCountryFromIp($ip);
        
        return $this->json([
            'ip' => $ip,
            'result' => $result,
            'client_ip' => $request->getClientIp(),
        ]);
    }
}
