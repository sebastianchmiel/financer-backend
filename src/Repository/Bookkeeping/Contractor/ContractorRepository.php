<?php

namespace App\Repository\Bookkeeping\Contractor;

use Doctrine\ORM\EntityRepository;
use App\Entity\Bookkeeping\Contractor\Contractor;
use App\Domain\Bookkeeping\Contractor\Filter\ContractorFilter;

class ContractorRepository  extends EntityRepository {
    /**
     * save object in datebase 
     * 
     * @param Contractor $contractor
     * 
     * @return void
     */
    public function save(Contractor $contractor) : void {
        $this->getEntityManager()->persist($contractor);
        $this->getEntityManager()->flush();
    }
    
    /**
     * remove item
     * 
     * @param Contractor $item
     * 
     * @return void
     */
    public function delete(Contractor $item) : void {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
    
    /**
     * find items by mylti parameters with pagination
     *
     * @param boolean $onlyCount           - true if return only count
     * @param ContractorFilter $filterData - data array with filter parameters
     * @param integer $offset              - pagination offset (optional)
     * @param integer $limit               - pagination limit (optional)
     * @param string  $orderColumn         - column to order (optional)
     * @param string  $orderDirection      - oreder direction (asc, desc) (optional)
     * 
     * @return int|array
     */
    public function findByMultiParameters($onlyCount, ContractorFilter $filterData, $offset = null, $limit = null, $orderColumn = null, $orderDirection = null) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(Contractor::class, 'i')
                        ->select(($onlyCount ? 'COUNT(i.id)' : 'i'));

        // filter - name
        if ($filterData->name) {
            $query->andWhere('i.name LIKE :FilterName')->setParameter('FilterName', '%'.$filterData->name.'%');
        }

        
        // order
        if (!in_array($orderColumn, ['name'])) {
            $orderColumn = 'name';
        }
        if ($orderColumn && $orderDirection) {
            $query->orderBy('i.' . $orderColumn, $orderDirection);
        }

        // limit
        if ($offset || $limit) {
            $query = $query->setFirstResult($offset)
                    ->setMaxResults($limit);
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
     * find all items for autocomplete
     * 
     * @return array
     */
    public function findAllForAutocomplete() : array {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(Contractor::class, 'i')
                        ->select('i.id, i.name AS title');
        
        try {
            return $query->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
}

