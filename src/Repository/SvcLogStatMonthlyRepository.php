<?php

namespace Svc\LogBundle\Repository;

use DateTime;
use Svc\LogBundle\Entity\SvcLogStatMonthly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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
   * truncate statistic table
   *
   * @return boolean
   */
  public function truncateStatMonthlyTable(): bool
  {
    $conn = $this->getEntityManager()->getConnection();
    $sql = "truncate table svc_log_stat_monthly";
    $stmt = $conn->prepare($sql);
    $stmt->executeStatement();
    return true;
  }

  /**
   * delete current perio in statistic table
   *
   * @param DateTime|null $startDate
   * @return integer
   */
  public function deleteCurrentData(?DateTime $startDate = null): int
  {
    $conn = $this->getEntityManager()->getConnection();
    $sql = "delete from svc_log_stat_monthly";
    if ($startDate) {
      $sql .= "  WHERE month >= :startDate";
    }
    $stmt = $conn->prepare($sql);
    if ($startDate) {
      $stmt->bindValue('startDate', $startDate->format("Y-m"));
    }
    $stmt->executeStatement();
    return $stmt->rowCount();
  }

  /**
   * aggregate and store logging data
   *
   * @param DateTime|null $startDate
   * @return integer
   */
  public function aggrData(?DateTime $startDate = null): int
  {
    $conn = $this->getEntityManager()->getConnection();

    $sql = "insert into svc_log_stat_monthly (month, source_id, source_type, log_level, log_count)";
    $sql .= " SELECT DATE_FORMAT(log_date, '%Y-%m') month, source_id, source_type, log_level, count(*) log_count FROM `svc_log`";
    if ($startDate) {
      $sql .= "  WHERE log_date >= :startDate";
    }
    $sql .= "  GROUP by month, source_id, source_type, log_level";

    try {
      $stmt = $conn->prepare($sql);
      if ($startDate) {
        $stmt->bindValue('startDate', $startDate->format("Y-m-d H:i:s"));
      }
      $stmt->executeStatement();
      return $stmt->rowCount();
    } catch (Exception $e) {
      dump($e->getMessage());
      die("Fehler");
      return -1;
    }
  }


  /**
   * fetch and pivot the statistic data
   *
   * @param array $months array with month like ['2021-06', ...]
   * @param [type] $sourceType
   * @return array
   */
  public function pivotData(array $months, $sourceType): array
  {
    return $this->createQueryBuilder('s')
      ->select("s.sourceID,
        SUM(CASE WHEN s.month='$months[0]' THEN s.logCount ELSE 0 END) AS month0,
        SUM(CASE WHEN s.month='$months[1]' THEN s.logCount ELSE 0 END) AS month1,
        SUM(CASE WHEN s.month='$months[2]' THEN s.logCount ELSE 0 END) AS month2,
        SUM(CASE WHEN s.month='$months[3]' THEN s.logCount ELSE 0 END) AS month3,
        SUM(CASE WHEN s.month='$months[4]' THEN s.logCount ELSE 0 END) AS month4
        ")
      ->where('s.sourceType = :sourceType')
      ->setParameter('sourceType', $sourceType)
      ->groupBy('s.sourceID')
      ->getQuery()
      ->getResult();
  }
}
