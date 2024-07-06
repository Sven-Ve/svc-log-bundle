<?php

namespace Svc\LogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Exception\DeleteAllLogsForbidden;
use Svc\LogBundle\Service\EventLog;

/**
 * @method SvcLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SvcLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SvcLog[]    findAll()
 * @method SvcLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SvcLogRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, SvcLog::class);
  }

  public const PAGINATOR_PER_PAGE = 15;

  /**
   * get a part of the logs for pagination.
   */
  public function getLogPaginator(int $offset, int $sourceID, ?int $sourceType = 0, ?int $logLevel = null): Paginator
  {
    $query = $this->createQueryBuilder('s')
      ->orderBy('s.id', 'DESC')
      ->setMaxResults(self::PAGINATOR_PER_PAGE)
      ->setFirstResult($offset)
      ->where('s.sourceID = :sourceID')
      ->andWhere('s.sourceType = :sourceType')
      ->setParameter('sourceID', $sourceID)
      ->setParameter('sourceType', $sourceType);

    if ($logLevel !== null and $logLevel !== EventLog::LEVEL_ALL) {
      $query
        ->andWhere('s.logLevel = :logLevel')
        ->setParameter('logLevel', $logLevel);
    }

    $query->getQuery();

    return new Paginator($query);
  }

  public function getLogPaginatorForViewer(int $offset, ?int $sourceID, ?int $sourceIDC, ?int $sourceType, ?int $sourceTypeC, ?int $logLevel, ?int $logLevelC, ?string $country): Paginator
  {
    // dd($sourceIDC);

    $query = $this->createQueryBuilder('s')
      ->orderBy('s.id', 'DESC')
      ->setMaxResults(self::PAGINATOR_PER_PAGE)
      ->setFirstResult($offset);

    if ($sourceID !== null) {
      $query
        ->andwhere('s.sourceID ' . $this->getComparisonOp($sourceIDC) . ' :sourceID')
        ->setParameter('sourceID', $sourceID);
    }

    if ($sourceType !== null) {
      $query
        ->andWhere('s.sourceType ' . $this->getComparisonOp($sourceTypeC) . ' :sourceType')
        ->setParameter('sourceType', $sourceType);
    }

    if ($logLevel !== null and $logLevel !== EventLog::LEVEL_ALL) {
      $query
        ->andWhere('s.logLevel  ' . $this->getComparisonOp($logLevelC) . '  :logLevel')
        ->setParameter('logLevel', $logLevel);
    }

    if ($country) {
      $query
        ->andWhere('s.country = :country')
        ->setParameter('country', $country);
    }

    $query->getQuery();

    return new Paginator($query);
  }

  /**
   * private function to convert to numeric comparison operator to the real operator
   *  1: "="
   *  2: ">"
   *  3: "<".
   */
  private function getComparisonOp(?int $numValue = null): string
  {
    if ($numValue === 2) {
      return '>';
    } elseif ($numValue === 3) {
      return '<';
    } else {
      return '=';
    }
  }

  /**
   * aggregate log entries by country for a specific ID.
   *
   * @return array<mixed>
   */
  public function aggrLogsByCountry(int $sourceID, ?int $sourceType = 0, ?int $logLevel = null): array
  {
    $query = $this->createQueryBuilder('s')
      ->select('s.country, count(s) as cntCountry')
      ->groupBy('s.country')
      ->orderBy('cntCountry', 'DESC')
      ->where('s.sourceID = :sourceID')
      ->andWhere('s.sourceType = :sourceType')
      ->setParameter('sourceID', $sourceID)
      ->setParameter('sourceType', $sourceType);

    if ($logLevel !== null and $logLevel !== EventLog::LEVEL_ALL) {
      $query
        ->andWhere('s.logLevel = :logLevel')
        ->setParameter('logLevel', $logLevel);
    }

    return $query->getQuery()
      ->getResult();
  }

  /**
   * aggregate log entries for the current day.
   *
   * @return array<mixed>
   */
  public function aggrLogsForCurrentDay(int $sourceType, ?int $logLevel = null): array
  {
    $query = $this->createQueryBuilder('s')
      ->select('s.sourceID, count(s) as cntDay')
      ->groupBy('s.sourceID')
      ->andWhere('s.sourceType = :sourceType')
      ->andWhere('s.logDate >= CURRENT_DATE()')
      ->setParameter('sourceType', $sourceType);

    if ($logLevel !== null and $logLevel !== EventLog::LEVEL_ALL) {
      $query
        ->andWhere('s.logLevel = :logLevel')
        ->setParameter('logLevel', $logLevel);
    }

    $results = $query->getQuery()
      ->getResult();

    $resultArray = [];
    foreach ($results as $result) {
      $resultArray[$result['sourceID']] = $result['cntDay'];
    }

    return $resultArray;
  }

  /**
   * purge old log data.
   */
  public function purgeOldData(\DateTime $keepDate, bool $dryRun): int
  {
    if ($dryRun) {
      $query = $this->createQueryBuilder('l')
        ->select('count(l) as cntRows')
        ->where('l.logDate < :keepDate')
        ->setParameter('keepDate', $keepDate)
        ->getQuery();

      $res = $query->getSingleResult();

      return $res['cntRows'];
    }

    $query = $this->createQueryBuilder('l')
      ->delete()
      ->where('l.logDate < :keepDate')
      ->setParameter('keepDate', $keepDate)
      ->getQuery();

    return $query->execute();
  }

  /**
   * @throws DeleteAllLogsForbidden
   */
  public function batchDelete(?int $sourceID = null, ?int $sourceType = null, ?int $userID = null, ?int $logLevel = null): int
  {
    if (!($sourceID . $sourceType . $userID . $logLevel)) {
      throw new DeleteAllLogsForbidden();
    }

    $query = $this->createQueryBuilder('l')
      ->delete();

    if ($sourceID !== null) {
      $query->andWhere('l.sourceID = :sourceID')
        ->setParameter('sourceID', $sourceID);
    }

    if ($sourceType !== null) {
      $query->andWhere('l.sourceType = :sourceType')
        ->setParameter('sourceType', $sourceType);
    }

    if ($userID !== null) {
      $query->andWhere('l.userID = :userID')
        ->setParameter('userID', $userID);
    }

    if ($logLevel !== null) {
      $query->andWhere('l.logLevel = :logLevel')
        ->setParameter('logLevel', $logLevel);
    }

    return $query->getQuery()
      ->execute();
  }
}
