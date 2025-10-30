<?php

namespace App\Controller;

use App\Service\VisitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class TrackingController extends AbstractController
{
    public function __construct(
        private VisitService $visitService
    ) {}

    #[Route('/track', name: 'track', methods: ['POST'])]
    public function track(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['url'])) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        // Get real IP (prioritize X-Forwarded-For for proxied requests)
        $ipAddress = $this->getRealIp($request);

        $visit = $this->visitService->createVisit(
            $data,
            $ipAddress,
            $request->headers->get('User-Agent') ?? 'Unknown'
        );

        return $this->json([
            'status' => 'success',
            'id' => $visit->getId()
        ]);
    }

    private function getRealIp(Request $request): string
    {
        // Check X-Forwarded-For header first (for proxied requests)
        $forwardedFor = $request->headers->get('X-Forwarded-For');
        if ($forwardedFor) {
            // Get first IP from comma-separated list
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }
        
        // Fallback to direct client IP
        return $request->getClientIp() ?? '0.0.0.0';
    }
}
