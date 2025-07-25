<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Svc\LogBundle\Entity\SvcLogStatMonthly;
use Svc\LogBundle\Enum\LogLevel;

/**
 * @method SvcLogStatMonthly|null find($id, $lockMode = null, $lockVersion = null)
 * @method SvcLogStatMonthly|null findOneBy(array $criteria, array $orderBy = null)
 * @method SvcLogStatMonthly[]    findAll()
 * @method SvcLogStatMonthly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SvcLogStatMonthlyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SvcLogStatMonthly::class);
    }

    /**
     * truncate statistic table.
     */
    public function truncateStatMonthlyTable(): bool
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'truncate table svc_log_stat_monthly';
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement();

        return true;
    }

    /**
     * delete current period in statistic table.
     */
    public function deleteCurrentData(?\DateTime $startDate = null): int|string
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'delete from svc_log_stat_monthly';
        if ($startDate) {
            $sql .= '  WHERE month >= :startDate';
        }
        $stmt = $conn->prepare($sql);
        if ($startDate) {
            $stmt->bindValue('startDate', $startDate->format('Y-m'));
        }

        return $stmt->executeStatement();
    }

    /**
     * aggregate and store logging data.
     */
    public function aggrData(?\DateTime $startDate = null): int|string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'insert into svc_log_stat_monthly (month, source_id, source_type, log_level, log_count)';
        $sql .= " SELECT DATE_FORMAT(log_date, '%Y-%m') month, source_id, source_type, log_level, count(*) log_count FROM `svc_log`";
        if ($startDate) {
            $sql .= '  WHERE log_date >= :startDate';
        }
        $sql .= '  GROUP by month, source_id, source_type, log_level';

        try {
            $stmt = $conn->prepare($sql);
            if ($startDate) {
                $stmt->bindValue('startDate', $startDate->format('Y-m-d H:i:s'));
            }

            return $stmt->executeStatement();
        } catch (\Exception $e) {
            dump($e->getMessage());

            return -1;
        }
    }

    /**
     * fetch and pivot the statistic data.
     *
     * @param array<string> $months array with month like ['2021-06', ...]
     *
     * @return array<mixed>
     */
    public function pivotData(array $months, int $sourceType, ?LogLevel $logLevel = null): array
    {
        $query = $this->createQueryBuilder('s')
          ->select("s.sourceID,
        SUM(CASE WHEN s.month='$months[0]' THEN s.logCount ELSE 0 END) AS month0,
        SUM(CASE WHEN s.month='$months[1]' THEN s.logCount ELSE 0 END) AS month1,
        SUM(CASE WHEN s.month='$months[2]' THEN s.logCount ELSE 0 END) AS month2,
        SUM(CASE WHEN s.month='$months[3]' THEN s.logCount ELSE 0 END) AS month3,
        SUM(CASE WHEN s.month='$months[4]' THEN s.logCount ELSE 0 END) AS month4,
        SUM(s.logCount)  as total
        ")
          ->where('s.sourceType = :sourceType')
          ->setParameter('sourceType', $sourceType);

        if ($logLevel !== null) {
            $query
              ->andwhere('s.logLevel = :logLevel')
              ->setParameter('logLevel', $logLevel);
        }

        return $query
          ->groupBy('s.sourceID')
          ->getQuery()
          ->getResult();
    }
}
