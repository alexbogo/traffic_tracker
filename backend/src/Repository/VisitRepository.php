<?php

namespace App\Repository;

use App\Entity\Visit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visit>
 *
 * @method Visit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visit[]    findAll()
 * @method Visit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    public function create(
        \App\Entity\Page $page,
        string $ipAddressHash,
        string $fingerprint,
        bool $isUnique,
        ?string $userAgent = null,
        ?string $referrer = null,
        ?string $countryCode = null,
        ?string $countryName = null,
        bool $isBot = false,
        ?string $deviceType = null,
        ?string $browser = null
    ): Visit {
        $visit = new Visit();
        $visit->setPage($page);
        $visit->setIpAddressHash($ipAddressHash);
        $visit->setVisitorFingerprint($fingerprint);
        $visit->setIsUnique($isUnique);
        $visit->setUserAgent($userAgent);
        $visit->setReferrer($referrer);
        $visit->setIpCountryCode($countryCode);
        $visit->setIpCountryName($countryName);
        $visit->setIsBot($isBot);
        $visit->setDeviceType($deviceType);
        $visit->setBrowser($browser);
        
        $this->save($visit);
        
        return $visit;
    }

    public function save(Visit $visit, bool $flush = true): void
    {
        $this->getEntityManager()->persist($visit);
        
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function hasRecentVisit(\App\Entity\Page $page, string $fingerprint, int $hours = 24): bool
    {
        $since = new \DateTimeImmutable(sprintf('-%d hours', $hours));

        $result = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.page = :page')
            ->andWhere('v.visitor_fingerprint = :fingerprint')
            ->andWhere('v.visited_at >= :since')
            ->setParameter('page', $page)
            ->setParameter('fingerprint', $fingerprint)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }

    public function findRecentByFingerprint(string $fingerprint, int $minutes = 30): ?Visit
    {
        $since = new \DateTimeImmutable(sprintf('-%d minutes', $minutes));

        return $this->createQueryBuilder('v')
            ->andWhere('v.visitor_fingerprint = :fingerprint')
            ->andWhere('v.visited_at >= :since')
            ->setParameter('fingerprint', $fingerprint)
            ->setParameter('since', $since)
            ->orderBy('v.visited_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countUniqueVisitors(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots = true
    ): int {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.visitor_fingerprint)')
            ->andWhere('v.page = :pageId')
            ->andWhere('v.visited_at BETWEEN :start AND :end')
            ->setParameter('pageId', $pageId)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if ($excludeBots) {
            $qb->andWhere('v.is_bot = :bot')
                ->setParameter('bot', false);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countTotalVisits(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots = true
    ): int {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.page = :pageId')
            ->andWhere('v.visited_at BETWEEN :start AND :end')
            ->setParameter('pageId', $pageId)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if ($excludeBots) {
            $qb->andWhere('v.is_bot = :bot')
                ->setParameter('bot', false);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getVisitsByCountry(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots = true
    ): array {
        $qb = $this->createQueryBuilder('v')
            ->select('v.ip_country_code', 'v.ip_country_name', 'COUNT(v.id) as visit_count')
            ->andWhere('v.page = :pageId')
            ->andWhere('v.visited_at BETWEEN :start AND :end')
            ->andWhere('v.ip_country_code IS NOT NULL')
            ->setParameter('pageId', $pageId)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->groupBy('v.ip_country_code', 'v.ip_country_name')
            ->orderBy('visit_count', 'DESC')
            ->setMaxResults(10);

        if ($excludeBots) {
            $qb->andWhere('v.is_bot = :bot')
                ->setParameter('bot', false);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get paginated visits for a page with filters
     */
    public function findPaginatedVisits(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots,
        int $limit,
        int $offset
    ): array {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.page = :pageId')
            ->andWhere('v.visited_at BETWEEN :start AND :end')
            ->setParameter('pageId', $pageId)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('v.visited_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($excludeBots) {
            $qb->andWhere('v.is_bot = :bot')
                ->setParameter('bot', false);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Count total visits matching filters (for pagination)
     */
    public function countVisitsWithFilters(
        int $pageId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $excludeBots
    ): int {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.page = :pageId')
            ->andWhere('v.visited_at BETWEEN :start AND :end')
            ->setParameter('pageId', $pageId)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if ($excludeBots) {
            $qb->andWhere('v.is_bot = :bot')
                ->setParameter('bot', false);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
