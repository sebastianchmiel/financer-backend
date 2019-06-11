<?php

namespace App\Service\Bookkeeping\Billing;

class BillingMonthDateResolver {

    /**
     * get base month date for billing month (first day of the month)
     * 
     * @param \DateTime $date
     * 
     * @return \DateTime
     */
    public function getMonthDate(\DateTime $date): \DateTime {
        return new \DateTime($date->format('Y-m') . '-01 00:00:00');
    }

}
