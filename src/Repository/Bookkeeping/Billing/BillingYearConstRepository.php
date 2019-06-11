<?php

namespace App\Repository\Bookkeeping\Billing;

use App\Entity\Bookkeeping\Billing\BillingYearConst;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Domain\Bookkeeping\Billing\Filter\BillingYearConstFilter;

/**
 * @method BillingYearConst|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingYearConst|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingYearConst[]    findAll()
 * @method BillingYearConst[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingYearConstRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillingYearConst::class);
    }

    /**
     * find items by mylti parameters with pagination
     *
     * @param boolean $onlyCount                 - true if return only count
     * @param BillingYearConstFilter $filterData - data array with filter parameters
     * 
     * @return int|array
     */
    public function findByMultiParameters($onlyCount, BillingYearConstFilter $filterData) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingYearConst::class, 'i')
                        ->select(($onlyCount ? 'COUNT(i.id)' : 'i'));

        // filter - year
        if ($filterData->year) {
            $query->andWhere('i.year = :Year')->setParameter('Year', $filterData->year);
        }
        
        // sort
        if (!$onlyCount && $filterData->sortBy && $filterData->sortDirection) {
            $query->orderBy('i.' . $filterData->sortBy, $filterData->sortDirection);
        }

        // limit
        $offset = (($filterData->currentPage - 1) * $filterData->perPage);
        if (!$onlyCount && ($offset || $filterData->perPage)) {
            $query = $query->setFirstResult($offset)
                    ->setMaxResults($filterData->perPage);
        }
        
        try {
            if ($onlyCount) {
                return $query->getQuery()->getSingleScalarResult();
            } else {
                return $query->getQuery()->getResult();
            }
        } catch (\Doctrine\ORM\NoResultException $ex) {
            if ($onlyCount) {
                return 0;
            } else {
                return [];
            }
        }
    }
    
    /**
     * save object in datebase 
     * 
     * @param BillingYearConst $item
     * 
     * @return void
     */
    public function save(BillingYearConst $item) : void {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }
    
    /**
     * remove item
     * 
     * @param BillingYearConst $item
     * 
     * @return void
     */
    public function delete(BillingYearConst $item) : void {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
