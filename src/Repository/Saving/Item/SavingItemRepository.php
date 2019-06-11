<?php

namespace App\Repository\Saving\Item;

use App\Entity\Saving\Item\SavingItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Domain\Saving\Item\Filter\SavingItemFilter;

/**
 * @method SavingItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavingItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavingItem[]    findAll()
 * @method SavingItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavingItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavingItem::class);
    }

    /**
     * find items by mylti parameters with pagination
     *
     * @param boolean $onlyCount             - true if return only count
     * @param SavingItemFilter $filterData   - data array with filter parameters
     * 
     * @return int|array
     */
    public function findByMultiParameters($onlyCount, SavingItemFilter $filterData) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(SavingItem::class, 'i')
                        ->select(($onlyCount ? 'COUNT(i.id)' : 'i'));

        // filter - name
        if ($filterData->name) {
            $query->andWhere('i.name LIKE :Name')->setParameter('Name', '%'.$filterData->name.'%');
        }

        if ($filterData->finished !== null) {
            $query->andWhere('i.finished = :Finished')->setParameter('Finished', $filterData->finished);
        }
        if ($filterData->used !== null) {
            $query->andWhere('i.used = :Used')->setParameter('Used', $filterData->used);
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
     * get items by id as array when key is item id and value is item object
     * 
     * @param array $itemsIds
     * 
     * @return array
     */
    public function getItemsById(array $itemsIds) {
        $query = $this->getEntityManager()->createQueryBuilder()
                    ->from(SavingItem::class, 'i')
                    ->select('i')
                    ->where('i.id IN (:ItemsId)')->setParameter('ItemsId', $itemsIds);
        
        try {
            $result = $query->getQuery()->getResult();
            if (!empty($result)) {
                $resultConverted = [];
                foreach ($result as $item) {
                    $resultConverted[$item->getId()] = $item;
                }
                return $resultConverted;
            }
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
        return [];
    }
    
    /**
     * save object in datebase 
     * 
     * @param SavingItem $item
     * 
     * @return void
     */
    public function save(SavingItem $item) : void {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }
    
    /**
     * remove item
     * 
     * @param SavingItem $item
     * 
     * @return void
     */
    public function delete(SavingItem $item) : void {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
