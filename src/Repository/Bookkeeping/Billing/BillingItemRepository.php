<?php

namespace App\Repository\Bookkeeping\Billing;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Bookkeeping\Billing\BillingItem;
use App\Domain\Bookkeeping\Billing\Filter\BillingMonthFilter;
use App\Entity\Bookkeeping\Billing\BillingMonth;
use App\Domain\Bookkeeping\Billing\Type\Types\IncomeType;

/**
 * @method BillingItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingItem[]    findAll()
 * @method BillingItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillingItem::class);
    }

    /**
     * find items by mylti parameters with pagination
     *
     * @param boolean $onlyCount             - true if return only count
     * @param BillingMonthFilter $filterData - data array with filter parameters
     * 
     * @return int|array
     */
    public function findByMultiParameters($onlyCount, BillingMonthFilter $filterData) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingItem::class, 'i')
                        ->select(($onlyCount ? 'COUNT(i.id)' : 'i'));

        // filter - name
        if ($filterData->billingMonthObject) {
            $query->leftJoin('i.billingMonth', 'm');
            $query->andWhere('m.id = :BillingMonth')->setParameter('BillingMonth', $filterData->billingMonthObject);
        }
        
        // filter - tags
        if (!empty($filterData->tags)) {
            $query->leftJoin('i.tags', 't');
            $query->andWhere('t.id IN (:Tags)')->setParameter('Tags', $filterData->tags);
        }
        
        // sort
        if (!$onlyCount && $filterData->sortBy && $filterData->sortDirection) {
            if ('contractor' === $filterData->sortBy) {
                $query->leftJoin('i.contractor', 'c')
                      ->orderBy('c.name', $filterData->sortDirection);
            } else {
                $query->orderBy('i.' . $filterData->sortBy, $filterData->sortDirection);
            }
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
     * change item field status (paid, copied, confirmed)
     * 
     * @param int $id
     * @param string $field
     * @param bool $value
     * 
     * @return void
     * 
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function changeItemFieldStatus(int $id, string $field, bool $value) : void {
        /* @var $item BillingItem */
        $item = $this->getEntityManager()->find(BillingItem::class, $id);
        if (!$item) {
            throw new \Doctrine\ORM\NoResultException();
        }
        
        switch ($field) {
            case 'paid':
                $item->setPaid($value);
                break;
            case 'copied':
                $item->setCopied($value);
                break;
            case 'confirmation':
                $item->setConfirmation($value);
                break;
            default:
                throw new \InvalidArgumentException();
        }
        
        $this->getEntityManager()->flush();
    }
    
    /**
     * get items with warnings (flags are zero)
     * 
     * @param BillingMonth $billingMonth
     * 
     * @return array
     */
    public function getItemsWithWarningInBillingMonth(BillingMonth $billingMonth) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingItem::class, 'i')
                        ->select('i.paid, i.copied, i.confirmation')
                        ->where('i.billingMonth = :BillingMonth')->setParameter('BillingMonth', $billingMonth)
                        ->andWhere('(i.paid = 0 OR i.copied = 0 OR i.confirmation = 0)')
                        ;
        try {
            return $query->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * get balance Gross for month
     * 
     * @param BillingMonth $billingMonth
     * 
     * @return int
     */
    public function getBalanceGrossForMonth(BillingMonth $billingMonth) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingItem::class, 'i')
                        ->select('SUM(i.amountGross) as bilanceGross')
                        ->where('i.billingMonth = :BillingMonth')->setParameter('BillingMonth', $billingMonth)
                        ;
        try {
            return $query->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return 0;
        }
    }
    
    /**
     * get billing items by tag id and date range
     * 
     * @param int $tagId
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * 
     * @return array
     */
    public function getBillingItemsByTagIdAndDateRange(int $tagId, \DateTime $dateFrom, \DateTime $dateTo) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingItem::class, 'i')
                        ->select('i.date, i.invoiceNumber, i.amountGross')
                        ->leftJoin('i.tags', 't')
                        ->where('t.id = :TagId')->setParameter('TagId', $tagId)
                        ->andWhere('i.date >= :DateFrom')->setParameter('DateFrom', $dateFrom)
                        ->andWhere('i.date <= :DateTo')->setParameter('DateTo', $dateTo)
                        ;
        try {
            return $query->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * get items paid in specified date range
     * 
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * 
     * @return array
     */
    public function getItemsPaidInDateRange(\DateTime $dateFrom, \DateTime $dateTo) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingItem::class, 'i')
                        ->select('i')
                        ->where('i.dateOfPaid >= :DateFrom')->setParameter('DateFrom', $dateFrom)
                        ->andWhere('i.dateOfPaid <= :DateTo')->setParameter('DateTo', $dateTo)
                        ;
        try {
            return $query->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * get last income items in date range with max
     * 
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param int $maxItems
     * 
     * @return array
     */
    public function getLastIncomeItems(\DateTime $dateFrom, \DateTime $dateTo, int $maxItems) {
        $query = $this->getEntityManager()->createQueryBuilder()
                        ->from(BillingItem::class, 'i')
                        ->select('i')
                        ->where('i.type = :Type')->setParameter('Type', IncomeType::TYPE_ID)
                        ->andWhere('i.dateOfPaid >= :DateFrom')->setParameter('DateFrom', $dateFrom)
                        ->andWhere('i.dateOfPaid <= :DateTo')->setParameter('DateTo', $dateTo)
                        ->orderBy('i.date', 'DESC')
                        ->setMaxResults($maxItems)
                        ;
        try {
            return $query->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return [];
        }
    }
    
    /**
     * save object in datebase 
     * 
     * @param BillingItem $item
     * 
     * @return void
     */
    public function save(BillingItem $item) : void {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }
    
    /**
     * remove item
     * 
     * @param BillingItem $item
     * 
     * @return void
     */
    public function delete(BillingItem $item) : void {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
