<?php

namespace App\Repository\Bookkeeping\Billing;

use App\Entity\Bookkeeping\Billing\BillingMonthSettlement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BillingMonthSettlement|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingMonthSettlement|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingMonthSettlement[]    findAll()
 * @method BillingMonthSettlement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingMonthSettlementRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillingMonthSettlement::class);
    }

    /**
     * get sum income tax for date range
     * 
     * @param \DateTime $dateBegin
     * @param \DateTime $dateEnd
     * 
     * @return int
     */
    public function getSumIncomeTaxForDateRange(\DateTime $dateBegin, \DateTime $dateEnd) : int {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingMonthSettlement::class, 'bms')
                        ->leftJoin('bms.billingMonth', 'bm')
                        ->select('SUM(bms.incomeTax) as sumIncomeTax')
                        ->where('bm.date >= :DateRangeBegin')->setParameter('DateRangeBegin', $dateBegin)
                        ->andWhere('bm.date <= :DateRangeEnd')->setParameter('DateRangeEnd', $dateEnd)
                        ;
        try {
            return $query->getQuery()->getSingleScalarResult() ?? 0;
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return 0;
        }
    }
    
    /**
     * get sum vat tax for date range
     * 
     * @param \DateTime $dateBegin
     * @param \DateTime $dateEnd
     * 
     * @return int
     */
    public function getSumVatTaxForDateRange(\DateTime $dateBegin, \DateTime $dateEnd) : int {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingMonthSettlement::class, 'bms')
                        ->leftJoin('bms.billingMonth', 'bm')
                        ->select('SUM(bms.vatTax) as sumVatTax')
                        ->where('bm.date >= :DateRangeBegin')->setParameter('DateRangeBegin', $dateBegin)
                        ->andWhere('bm.date <= :DateRangeEnd')->setParameter('DateRangeEnd', $dateEnd)
                        ;
        try {
            return $query->getQuery()->getSingleScalarResult() ?? 0;
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return 0;
        }
    }
    
    /**
     * get settlement data for chart in date range
     * 
     * @param \DateTime $dateBegin
     * @param \DateTime $dateEnd
     * 
     * @return array
     */
    public function getSettlementDataForChartInDateRange(\DateTime $dateBegin, \DateTime $dateEnd) : array {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingMonthSettlement::class, 'bms')
                        ->leftJoin('bms.billingMonth', 'bm')
                        ->select('bm.date, bms.costSum, bms.incomeSum, bms.realIncome, bms.incomeTax, bms.vatTax')
                        ->where('bm.date >= :DateRangeBegin')->setParameter('DateRangeBegin', $dateBegin)
                        ->andWhere('bm.date <= :DateRangeEnd')->setParameter('DateRangeEnd', $dateEnd)
                        ;
        try {
            return $query->getQuery()->getArrayResult() ?? [];
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * save item id datebase
     * 
     * @param BillingMonthSettlement $item
     * @param bool $flush
     * 
     * @return void
     */
    public function save(BillingMonthSettlement $item, bool $flush = true) : void {
        $this->getEntityManager()->persist($item);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * flush changes to database
     */
    public function flush() {
        $this->getEntityManager()->flush();
    }
}
