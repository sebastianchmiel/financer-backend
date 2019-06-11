<?php

namespace App\Repository\Bookkeeping\Tag;

use App\Entity\Bookkeeping\Tag\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Domain\Bookkeeping\Tag\Filter\TagFilter;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * find items by mylti parameters with pagination
     *
     * @param boolean $onlyCount             - true if return only count
     * @param TagFilter $filterData - data array with filter parameters
     * 
     * @return int|array
     */
    public function findByMultiParameters($onlyCount, TagFilter $filterData) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(Tag::class, 'i')
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
     * find all items for autocomplete
     * 
     * @return array
     */
    public function findAllForAutocomplete() : array {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(Tag::class, 'i')
                        ->select('i.id, i.name AS title, i.backgroundColor, i.fontColor');
        
        try {
            return $query->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * save object in datebase 
     * 
     * @param Tag $item
     * 
     * @return void
     */
    public function save(Tag $item) : void {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }
    
    /**
     * remove item
     * 
     * @param Tag $item
     * 
     * @return void
     */
    public function delete(Tag $item) : void {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
