<?php

namespace App\Repository;

use App\Entity\ApiResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApiResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiResponse[]    findAll()
 * @method ApiResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiResponse::class);
    }

    // /**
    //  * @return ApiResponse[] Returns an array of ApiResponse objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ApiResponse
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
