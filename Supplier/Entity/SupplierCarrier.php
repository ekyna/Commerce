<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierCarrierInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class SupplierCarrier
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierCarrier extends AbstractResource implements SupplierCarrierInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var TaxInterface
     */
    protected $tax;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New carrier';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @inheritDoc
     */
    public function setTax(TaxInterface $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }
}
