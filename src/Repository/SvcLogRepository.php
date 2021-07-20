<?php

namespace Svc\LogBundle\Repository;

use Svc\LogBundle\Entity\SvcLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
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

  public const PAGINATOR_PER_PAGE = 10;

  /**
   * get a part of the logs for pagination
   *
   * @param integer $offset
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @param integer|null $logLevel
   * @return Paginator
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


  /**
   * aggragete log entries by country for a specific ID
   *
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @param integer|null $logLevel
   * @return array
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
}
