<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var float
     */
    protected $rate;

    /**
     * @var CountryInterface
     */
    protected $country;

    /**
     * @var StateInterface
     */
    protected $state;

    /**
     * @var ArrayCollection|TaxRuleInterface[]
     */
    protected $taxRules;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->rate = 0.;
        $this->taxRules = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New tax';
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
    public function setCode(string $code): TaxInterface
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
    public function setName(string $name): TaxInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @inheritdoc
     */
    public function setRate(float $rate): TaxInterface
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountry(): ?CountryInterface
    {
        return $this->country;
    }

    /**
     * @inheritdoc
     */
    public function setCountry(CountryInterface $country): TaxInterface
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState(): ?StateInterface
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState(StateInterface $state = null): TaxInterface
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMode()
    {
        return AdjustmentModes::MODE_PERCENT;
    }

    /**
     * @inheritdoc
     */
    public function getDesignation()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->getRate();
    }

    /**
     * @inheritdoc
     */
    public function isImmutable()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSource()
    {
        if (!$this->id) {
            return null;
        }

        return "tax:{$this->id}";
    }
}
