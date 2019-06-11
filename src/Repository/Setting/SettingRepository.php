<?php

namespace App\Repository\Setting;

use App\Entity\Setting\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SettingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    /**
     * save object in datebase 
     * 
     * @param Tag $item
     * 
     * @return void
     */
    public function save(Setting $item) : void {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }
    
    /**
     * remove item
     * 
     * @param Setting $item
     * 
     * @return void
     */
    public function delete(Setting $item) : void {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
