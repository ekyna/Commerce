<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;

/**
 * Class Total
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Tax implements TaxInterface
{
    protected ?int              $id      = null;
    protected ?string           $code    = null;
    protected ?string           $name    = null;
    protected Decimal           $rate;
    protected ?CountryInterface $country = null;
    protected ?StateInterface   $state   = null;

    /** @var Collection|array<TaxRuleInterface> */
    protected Collection $taxRules;

    public function __construct()
    {
        $this->rate = new Decimal(0);
        $this->taxRules = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->name ?: 'New tax';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): TaxInterface
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): TaxInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getRate(): Decimal
    {
        return $this->rate;
    }

    public function setRate(Decimal $rate): TaxInterface
    {
        $this->rate = $rate;

        return $this;
    }

    public function getCountry(): ?CountryInterface
    {
        return $this->country;
    }

    public function setCountry(CountryInterface $country): TaxInterface
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): ?StateInterface
    {
        return $this->state;
    }

    public function setState(?StateInterface $state): TaxInterface
    {
        $this->state = $state;

        return $this;
    }

    public function getMode(): string
    {
        return AdjustmentModes::MODE_PERCENT;
    }

    public function getDesignation(): string
    {
        return $this->__toString();
    }

    public function getAmount(): Decimal
    {
        return $this->rate;
    }

    public function isImmutable(): bool
    {
        return true;
    }

    public function getSource(): ?string
    {
        if ($this->id) {
            return "tax:$this->id";
        }

        return null;
    }
}
