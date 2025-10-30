<?php

namespace App\Controller;

use App\Service\StatsAggregator;
use App\Repository\PageRepository;
use App\Repository\VisitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    public function __construct(
        private StatsAggregator $statsAggregator,
        private PageRepository $pageRepository,
        private VisitRepository $visitRepository
    ) {
    }

    /**
     * Get all tracked pages with basic stats
     * GET /api/pages
     */
    #[Route('/pages', name: 'api_pages', methods: ['GET'])]
    public function getPages(): JsonResponse
    {
        try {
            $pages = $this->statsAggregator->getAllPagesWithStats();

            // Return pages array directly (frontend expects array, not wrapped object)
            return $this->json($pages);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to fetch pages',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed statistics for a specific page
     * GET /api/pages/{id}/stats?start_date=2024-01-01&end_date=2024-12-31&exclude_bots=1
     */
    #[Route('/pages/{id}/stats', name: 'api_page_stats', methods: ['GET'])]
    public function getPageStats(int $id, Request $request): JsonResponse
    {
        try {
            // Validate page exists
            $page = $this->pageRepository->find($id);
            if (!$page) {
                return $this->json([
                    'success' => false,
                    'error' => 'Page not found'
                ], 404);
            }

            // Parse date parameters with defaults
            $startDate = $this->parseDateParameter($request->query->get('start_date'), '-30 days');
            $endDate = $this->parseDateParameter($request->query->get('end_date'), 'now');
            // Make end date inclusive (end of day)
            $endDate = $endDate->setTime(23, 59, 59);
            $excludeBots = $request->query->getBoolean('exclude_bots', true);

            // Get comprehensive stats
            $stats = $this->statsAggregator->getPageStats($id, $startDate, $endDate, $excludeBots);

            // Format response for frontend
            $response = [
                'unique_visitors' => $stats['unique_visitors'],
                'total_visits' => $stats['total_visits'],
                'countries' => array_map(function($item) {
                    return [
                        'country_code' => $item['ip_country_code'],
                        'country_name' => $item['ip_country_name'],
                        'visitors' => (int)$item['visit_count'],
                    ];
                }, $stats['visits_by_country']),
                'countries_count' => count($stats['visits_by_country']),
                'time_series' => array_map(function($item) {
                    return [
                        'date' => $item['period'],
                        'unique_visitors' => (int)$item['visits'], // simplified for now
                        'total_visits' => (int)$item['visits'],
                    ];
                }, $stats['time_series']),
            ];

            return $this->json($response);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to fetch page statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated visit history for a specific page
     * GET /api/pages/{id}/visits?page=1&limit=50&start_date=2024-01-01&end_date=2024-12-31&exclude_bots=1
     */
    #[Route('/pages/{id}/visits', name: 'api_page_visits', methods: ['GET'])]
    public function getPageVisits(int $id, Request $request): JsonResponse
    {
        try {
            // Validate page exists
            $page = $this->pageRepository->find($id);
            if (!$page) {
                return $this->json([
                    'success' => false,
                    'error' => 'Page not found'
                ], 404);
            }

            // Parse pagination parameters
            $pageNum = max(1, $request->query->getInt('page', 1));
            $limit = min(100, max(1, $request->query->getInt('limit', 50)));
            $offset = ($pageNum - 1) * $limit;

            // Parse date parameters
            $startDate = $this->parseDateParameter($request->query->get('start_date'), '-30 days');
            $endDate = $this->parseDateParameter($request->query->get('end_date'), 'now');
            $endDate = $endDate->setTime(23, 59, 59);
            $excludeBots = $request->query->getBoolean('exclude_bots', true);

            // Get paginated visits from repository
            $visits = $this->visitRepository->findPaginatedVisits(
                $id,
                $startDate,
                $endDate,
                $excludeBots,
                $limit,
                $offset
            );

            // Get total count for pagination
            $totalCount = $this->visitRepository->countVisitsWithFilters(
                $id,
                $startDate,
                $endDate,
                $excludeBots
            );

            // Format visits for response
            $formattedVisits = array_map(function($visit) {
                return [
                    'id' => $visit->getId(),
                    'visited_at' => $visit->getVisitedAt()?->format('c'),
                    'country_code' => $visit->getIpCountryCode(),
                    'country_name' => $visit->getIpCountryName(),
                    'referrer' => $visit->getReferrer(),
                    'is_bot' => $visit->isBot(),
                    'is_unique' => $visit->isUnique(),
                    'user_agent' => $visit->getUserAgent(),
                ];
            }, $visits);

            return $this->json([
                'success' => true,
                'data' => $formattedVisits,
                'pagination' => [
                    'page' => $pageNum,
                    'limit' => $limit,
                    'total' => $totalCount,
                    'total_pages' => (int) ceil($totalCount / $limit),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to fetch visits',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Legacy endpoint - Get statistics (redirects to first page stats if no page_id provided)
     * GET /api/stats?page_id=1&start_date=2024-01-01&end_date=2024-12-31&exclude_bots=1
     */
    #[Route('/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $pageId = $request->query->getInt('page_id');

            if (!$pageId) {
                // Get first available page
                $firstPage = $this->pageRepository->findOneBy([], ['id' => 'ASC']);
                if (!$firstPage) {
                    return $this->json([
                        'success' => false,
                        'error' => 'No pages tracked yet'
                    ], 404);
                }
                $pageId = $firstPage->getId();
            }

            // Redirect to page stats endpoint
            return $this->forward('App\Controller\DashboardController::getPageStats', [
                'id' => $pageId,
                'request' => $request
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Parse date parameter with fallback
     */
    private function parseDateParameter(?string $date, string $fallback): \DateTimeImmutable
    {
        if (!$date) {
            return new \DateTimeImmutable($fallback);
        }

        try {
            // Parse the date at start of day (00:00:00) in UTC
            $parsed = new \DateTimeImmutable($date . ' 00:00:00', new \DateTimeZone('UTC'));
            return $parsed;
        } catch (\Exception $e) {
            return new \DateTimeImmutable($fallback);
        }
    }
}
