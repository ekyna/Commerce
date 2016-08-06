<?php

namespace Ekyna\Component\Commerce\Customer\EventListener;

use Ekyna\Component\Commerce\Customer\Model\CustomerEventInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class CustomerListener
 * @package Ekyna\Component\Commerce\Customer\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerListener
{
    /**
     * Pre create event handler.
     *
     * @param CustomerEventInterface $event
     */
    public function onPreCreate(CustomerEventInterface $event)
    {
        $customer = $event->getCustomer();

        $customer
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->syncCustomerCompanyName($customer);
    }

    /**
     * Pre update event handler.
     *
     * @param CustomerEventInterface $event
     */
    public function onPreUpdate(CustomerEventInterface $event)
    {
        $customer = $event->getCustomer();

        $customer->setUpdatedAt(new \DateTime());

        $this->syncCustomerCompanyName($customer);
    }

    /**
     * Pre delete event handler.
     *
     * @param CustomerEventInterface $event
     */
    public function onPreDelete(CustomerEventInterface $event)
    {

    }

    /**
     * Synchronises the company name from parent to children.
     *
     * @param CustomerInterface $customer The customer who's company or parent property has changed.
     *
     * @throws InvalidArgumentException
     */
    protected function syncCustomerCompanyName(CustomerInterface $customer)
    {
        if ($customer->hasParent()) {
            if (0 === strlen($company = $customer->getParent()->getCompany())) {
                throw new InvalidArgumentException('Parent company name is empty.');
            }

            if ($company != $customer->getCompany()) {
                $customer->setCompany($company);
            }
        } elseif ($customer->hasChildren()) {
            if (0 === strlen($company = $customer->getCompany())) {
                throw new InvalidArgumentException('Parent company name is empty.');
            }

            foreach ($customer->getChildren() as $child) {
                if ($company != $child->getCompany()) {
                    $child->setCompany($company);
                    $recompute[] = $child;
                }
            }
        }
    }
}
