<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Bundle\CoreBundle\Model\UploadableInterface;
use Ekyna\Bundle\CoreBundle\Model\UploadableTrait;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class CustomerLogo
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerLogo implements UploadableInterface
{
    use UploadableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var CustomerInterface
     */
    private $customer;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return CustomerLogo
     */
    public function setCustomer(CustomerInterface $customer = null): CustomerLogo
    {
        if ($customer !== $this->customer) {
            if ($this->customer) {
                $this->customer->setBrandLogo(null);
            }

            $this->customer = $customer;

            $this->customer->setBrandLogo($this);
        }

        return $this;
    }
}
