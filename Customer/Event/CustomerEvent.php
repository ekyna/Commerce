<?php

namespace Ekyna\Component\Commerce\Customer\Event;

use Ekyna\Component\Commerce\Customer\Model\CustomerEventInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class CustomerEvent
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerEvent implements CustomerEventInterface
{
    /**
     * @var CustomerInterface
     */
    private $customer;


    /**
     * Constructor.
     *
     * @param CustomerInterface $customer
     */
    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
