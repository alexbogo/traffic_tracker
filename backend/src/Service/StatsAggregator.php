<?php

namespace App\Service;

use App\Repository\VisitRepository;
use App\Repository\PageRepository;
use Doctrine\DBAL\Connection;

/**
 * Service for aggregating and calculating visit statistics
 */
class StatsAggregator
{
    public function __construct(
        private VisitRepository $visitRepository,
        private PageRepository $pageRepository,
        private Connection $connection
    ) {
    }

    /**
     * Get unique visitors count for a page within date range
     */
    public function getUniqueVisitors(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots = true
    ): int {
        return $this->visitRepository->countUniqueVisitors($pageId, $startDate, $endDate, $excludeBots);
    }

    /**
     * Get total visits count for a page within date range
     */
    public function getTotalVisits(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots = true
    ): int {
        return $this->visitRepository->countTotalVisits($pageId, $startDate, $endDate, $excludeBots);
    }

    /**
     * Get visits grouped by country
     * Returns top 10 countries with visit counts
     */
    public function getVisitsByCountry(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots = true
    ): array {
        return $this->visitRepository->getVisitsByCountry($pageId, $startDate, $endDate, $excludeBots);
    }

    /**
     * Get time series data for charting
     * Returns visits grouped by day/hour
     * Uses native SQL for DATE_FORMAT support
     */
    public function getTimeSeriesData(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $interval = 'day',
        bool $excludeBots = true
    ): array {
        // Choose date format based on interval
        $dateFormat = match($interval) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $sql = "
            SELECT
                DATE_FORMAT(visited_at, :dateFormat) as period,
                COUNT(id) as visits
            FROM visits
            WHERE page_id = :pageId
            AND visited_at BETWEEN :startDate AND :endDate
        ";

        $params = [
            'dateFormat' => $dateFormat,
            'pageId' => $pageId,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
        ];

        if ($excludeBots) {
            $sql .= " AND is_bot = :isBot";
            $params['isBot'] = 0;
        }

        $sql .= " GROUP BY period ORDER BY period ASC";

        return $this->connection->fetchAllAssociative($sql, $params);
    }

    /**
     * Get complete stats for a page
     */
    public function getPageStats(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots = true
    ): array {
        return [
            'unique_visitors' => $this->getUniqueVisitors($pageId, $startDate, $endDate, $excludeBots),
            'total_visits' => $this->getTotalVisits($pageId, $startDate, $endDate, $excludeBots),
            'visits_by_country' => $this->getVisitsByCountry($pageId, $startDate, $endDate, $excludeBots),
            'time_series' => $this->getTimeSeriesData($pageId, $startDate, $endDate, 'day', $excludeBots),
        ];
    }

    /**
     * Get all tracked pages with basic stats
     */
    public function getAllPagesWithStats(): array
    {
        $pages = $this->pageRepository->findAllWithStats();

        $result = [];
        foreach ($pages as $item) {
            $page = is_array($item) ? $item[0] : $item;
            $visitCount = is_array($item) ? $item['visit_count'] : 0;

            $result[] = [
                'id' => $page->getId(),
                'url' => $page->getUrl(),
                'title' => $page->getTitle(),
                'total_visits' => $visitCount,
                'created_at' => $page->getCreatedAt()?->format('c'),
                'updated_at' => $page->getUpdatedAt()?->format('c'),
            ];
        }

        return $result;
    }
}
