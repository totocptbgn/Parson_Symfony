<?php

namespace App\Repository;

use App\Entity\ErrorController;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ErrorController|null find($id, $lockMode = null, $lockVersion = null)
 * @method ErrorController|null findOneBy(array $criteria, array $orderBy = null)
 * @method ErrorController[]    findAll()
 * @method ErrorController[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ErrorControllerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ErrorController::class);
    }

    // /**
    //  * @return ErrorController[] Returns an array of ErrorController objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ErrorController
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
