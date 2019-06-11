<?php

namespace App\Repository\Saving\Item;

use App\Entity\Saving\Item\SavingItemHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SavingItemHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavingItemHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavingItemHistory[]    findAll()
 * @method SavingItemHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavingItemHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavingItemHistory::class);
    }

    /**
     * get history items from date range as array
     * 
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * 
     * @return array
     */
    public function getItemsInDateRangeAsArray(\DateTime $dateFrom, \DateTime $dateTo) : array {
        $query = $this->getEntityManager()->createQueryBuilder()
                    ->from(SavingItemHistory::class, 'i')
                    ->select('i.date, i.amount')
                    ->where('i.date >= :DateFrom')->setParameter('DateFrom', $dateFrom)
                    ->andWhere('i.date <= :DateTo')->setParameter('DateTo', $dateTo);
        
        try {
            return $query->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
        }
        return [];
    }
    
    
    /**
     * persist object in datebase 
     * 
     * @param SavingItemHistory $item
     * 
     * @return void
     */
    public function persist(SavingItemHistory $item) : void {
        $this->getEntityManager()->persist($item);
    }
    
    /**
     * flush 
     * 
     * @return void
     */
    public function flush() : void {
        $this->getEntityManager()->flush();
    }
}
