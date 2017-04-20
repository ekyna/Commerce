<?php

declare(strict_types=1);

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
    protected ?int    $id      = null;
    protected ?string $code    = null;
    protected ?string $name    = null;
    protected bool    $default = false;

    /** @var Collection|TaxInterface[] */
    protected Collection $taxes;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->taxes = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->name ?: 'New tax group';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): TaxGroupInterface
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): TaxGroupInterface
    {
        $this->name = $name;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): TaxGroupInterface
    {
        $this->default = $default;

        return $this;
    }

    public function hasTaxes(): bool
    {
        return 0 < $this->taxes->count();
    }

    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function hasTax(TaxInterface $tax): bool
    {
        return $this->taxes->contains($tax);
    }

    public function addTax(TaxInterface $tax): TaxGroupInterface
    {
        if (!$this->hasTax($tax)) {
            $this->taxes->add($tax);
        }

        return $this;
    }

    public function removeTax(TaxInterface $tax): TaxGroupInterface
    {
        if ($this->hasTax($tax)) {
            $this->taxes->removeElement($tax);
        }

        return $this;
    }

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
