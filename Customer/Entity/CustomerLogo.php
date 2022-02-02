<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\UploadableInterface;
use Ekyna\Component\Resource\Model\UploadableTrait;

/**
 * Class CustomerLogo
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerLogo extends AbstractResource implements UploadableInterface
{
    use UploadableTrait;

    private ?CustomerInterface $customer = null;

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): CustomerLogo
    {
        if ($customer === $this->customer) {
            return $this;
        }

        if ($previous = $this->customer) {
            $this->customer = null;
            $previous->setBrandLogo(null);
        }

        if ($this->customer = $customer) {
            $this->customer->setBrandLogo($this);
        }

        return $this;
    }
}
