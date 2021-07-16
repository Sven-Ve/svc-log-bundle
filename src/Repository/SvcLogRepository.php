<?php

namespace Svc\LogBundle\Repository;

use Svc\LogBundle\Entity\SvcLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

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

  public const PAGINATOR_PER_PAGE = 2;

  public function getLogPaginator(int $offset, int $sourceID, ?int $sourceType = 0, ?int $logLevel = null): Paginator
  {
    $query = $this->createQueryBuilder('s')
      ->orderBy('s.id', 'DESC')
      ->setMaxResults(self::PAGINATOR_PER_PAGE)
      ->setFirstResult($offset)
              ->where('s.sourceID = :sourceID')
              ->setParameter('sourceID', $sourceID)
      ->getQuery();
    return new Paginator($query);
  }
}
