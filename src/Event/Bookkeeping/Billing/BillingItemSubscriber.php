<?php

namespace App\Event\Bookkeeping\Billing;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\Bookkeeping\Billing\BillingItem;

class BillingItemSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->checkPaid($args);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->checkPaid($args);
    }

    public function checkPaid(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof BillingItem) {
            $entity->setPaid($entity->getDateOfPaid() ? true : false);
        }
    }
}