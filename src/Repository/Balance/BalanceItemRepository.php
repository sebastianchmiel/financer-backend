<?php

namespace App\Repository\Balance;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Balance\BalanceItem;
use App\Domain\Balance\BankStatement\Model\BankStatementModel;
use App\Domain\Balance\Filter\BalanceMonthFilter;

/**
 * @method BalanceItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method BalanceItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method BalanceItem[]    findAll()
 * @method BalanceItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BalanceItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BalanceItem::class);
    }

    /**
     * getItemsForMonthDate
     * 
     * @param \DateTime $monthDate
     * 
     * @return array
     */
    public function getItemsForMonthDate(BalanceMonthFilter $filterData) {
        $query = $this->createQueryBuilder('i')
            ->where('i.dateOperation >= :dateBegin')->setParameter('dateBegin', $filterData->monthDate)
            ->andWhere('i.dateOperation <= :dateEnd')->setParameter('dateEnd', new \DateTime($filterData->monthDate->format('Y-m-t').' 23:59:59.999999'))
        ;

        // filter - name
        if (!empty($filterData->tags)) {
            $query->leftJoin('i.tag', 't');
            $query->andWhere('t.id IN (:tags)')->setParameter('tags', $filterData->tags);
        }
        
        try {
            return $query->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * get item by hash
     * 
     * @param string $hash
     * 
     * @return BalanceItem|null
     */
    public function getItemByHash($hash) {
        return $this->createQueryBuilder('i')
            ->andWhere('i.hash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    /**
     * save item from bank statement model
     * 
     * @param BankStatementModel $item
     * @param boolean $flush - flag if flush imidiatly to datebase (default TRUE)
     * 
     * @return boolean
     */
    public function saveBankStatementModel(BankStatementModel $item, $flush = true) {
        // check if already exist
        if ($this->getItemByHash($item->getHash())) {
            return false;
        }
        
        $entity = new BalanceItem();
        $entity->setDateOperation($item->getDateOperation());
        $entity->setDatePosting($item->getDatePosting());
        $entity->setDescription($item->getDescription());
        $entity->setTitle($item->getTitle());
        $entity->setSenderReceiver($item->getSenderReceiver());
        $entity->setAccountNumber($item->getAccountNumber());
        $entity->setAmount($item->getAmount());
        $entity->setBalance($item->getBalance());
        $entity->setHash($item->getHash());
        
        $this->getEntityManager()->persist($entity);
        
        if ($flush) {
            $this->flush();
        }
        
        return true;
    }
    
    /**
     * save data in datebase
     */
    public function flush() {
        $this->getEntityManager()->flush();
    }
}
