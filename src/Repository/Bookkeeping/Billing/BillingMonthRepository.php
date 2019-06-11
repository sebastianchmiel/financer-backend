<?php

namespace App\Repository\Bookkeeping\Billing;

use App\Entity\Bookkeeping\Billing\BillingMonth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BillingMonth|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingMonth|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingMonth[]    findAll()
 * @method BillingMonth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingMonthRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillingMonth::class);
    }

    /**
     * save item id datebase
     * 
     * @param BillingMonth $item
     * 
     * @return void
     */
    public function save(BillingMonth $item) : void {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }
    
}
