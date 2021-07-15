<?php

namespace Svc\LogBundle\Repository;

use Svc\LogBundle\Entity\SvcLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

}
