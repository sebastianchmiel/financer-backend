<?php

namespace App\Repository\Bookkeeping\Billing;

use App\Entity\Bookkeeping\Billing\BillingItemPosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BillingItemPosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingItemPosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingItemPosition[]    findAll()
 * @method BillingItemPosition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingItemPositionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillingItemPosition::class);
    }

//    /**
//     * @return BillingItemPosition[] Returns an array of BillingItemPosition objects
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
    public function findOneBySomeField($value): ?BillingItemPosition
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
