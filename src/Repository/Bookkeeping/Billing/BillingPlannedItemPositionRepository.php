<?php

namespace App\Repository\Bookkeeping\Billing;

use App\Entity\Bookkeeping\Billing\BillingPlannedItemPosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BillingPlannedItemPosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingPlannedItemPosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingPlannedItemPosition[]    findAll()
 * @method BillingPlannedItemPosition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingPlannedItemPositionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillingPlannedItemPosition::class);
    }

//    /**
//     * @return BillingPlannedItemPosition[] Returns an array of BillingPlannedItemPosition objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BillingPlannedItemPosition
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
