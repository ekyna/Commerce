<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;

/**
 * Class TaxGroup
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroup implements TaxGroupInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $default;

    /**
     * @var ArrayCollection|TaxInterface[]
     */
    protected $taxes;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->default = false;
        $this->taxes = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @inheritdoc
     */
    public function setDefault($default)
    {
        $this->default = (bool)$default;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxes()
    {
        return 0 < $this->taxes->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @inheritdoc
     */
    public function hasTax(TaxInterface $tax)
    {
        return $this->taxes->contains($tax);
    }

    /**
     * @inheritdoc
     */
    public function addTax(TaxInterface $tax)
    {
        if (!$this->hasTax($tax)) {
            $this->taxes->add($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTax(TaxInterface $tax)
    {
        if ($this->hasTax($tax)) {
            $this->taxes->removeElement($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxes(array $taxes)
    {
        foreach ($this->taxes as $tax) {
            $this->removeTax($tax);
        }

        foreach ($taxes as $tax) {
            $this->addTax($tax);
        }

        return $this;
    }
}
