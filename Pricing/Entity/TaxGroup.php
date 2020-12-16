<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    protected $code;

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
    public function __toString(): string
    {
        return $this->name ?: 'New tax group';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setCode(string $code): TaxGroupInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): TaxGroupInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @inheritdoc
     */
    public function setDefault(bool $default): TaxGroupInterface
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxes(): bool
    {
        return 0 < $this->taxes->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    /**
     * @inheritdoc
     */
    public function hasTax(TaxInterface $tax): bool
    {
        return $this->taxes->contains($tax);
    }

    /**
     * @inheritdoc
     */
    public function addTax(TaxInterface $tax): TaxGroupInterface
    {
        if (!$this->hasTax($tax)) {
            $this->taxes->add($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTax(TaxInterface $tax): TaxGroupInterface
    {
        if ($this->hasTax($tax)) {
            $this->taxes->removeElement($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxes(array $taxes): TaxGroupInterface
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
