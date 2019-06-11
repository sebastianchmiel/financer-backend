<?php

namespace App\Repository\Bookkeeping\Billing;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem;
use App\Domain\Bookkeeping\Billing\Filter\BillingPlannedItemFilter;

class BillingPlannedItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillingPlannedItem::class);
    }

    
    /**
     * find items by mylti parameters with pagination
     *
     * @param boolean $onlyCount                  - true if return only count
     * @param BillingPlannedItemFilter $filterData - data array with filter parameters
     * 
     * @return int|array
     */
    public function findByMultiParameters($onlyCount, BillingPlannedItemFilter $filterData) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingPlannedItem::class, 'i')
                        ->select(($onlyCount ? 'COUNT(i.id)' : 'i'));

        // filter - name
        if ($filterData->name) {
            $query->andWhere('i.name LIKE :Name')->setParameter('Name', '%'.$filterData->name.'%');
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
     * get planned items for month
     * 
     * @param \DateTime $monthDate
     * 
     * @return array
     */
    public function getPlannedItemsForMonth(\DateTime $monthDate) : array {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingPlannedItem::class, 'i')
                        ->select('i')
                        ->where('i.dateFrom <= :DateMonth')
                        ->andWhere('(i.dateTo IS NULL OR i.dateTo >= :DateMonth)')
                        ->setParameter('DateMonth', $monthDate);
        
        try {
            return $query->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * save object in datebase 
     * 
     * @param BillingPlannedItem $item
     * 
     * @return void
     */
    public function save(BillingPlannedItem $item) : void {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }
    
    /**
     * remove item
     * 
     * @param BillingPlannedItem $item
     * 
     * @return void
     */
    public function delete(BillingPlannedItem $item) : void {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
