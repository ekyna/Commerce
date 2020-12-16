<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class Group
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CustomerGroupTranslationInterface translate($locale = null, $create = false)
 */
class CustomerGroup extends AbstractTranslatable implements CustomerGroupInterface
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
     * @var bool
     */
    protected $default;

    /**
     * @var bool
     */
    protected $business;

    /**
     * @var bool
     */
    protected $registration;

    /**
     * @var bool
     */
    protected $quoteAllowed;

    /**
     * @var bool
     */
    protected $loyalty;

    /**
     * @var string|null
     */
    protected $vatDisplayMode;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->default = false;
        $this->business = false;
        $this->registration = false;
        $this->quoteAllowed = false;
        $this->loyalty = false;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New customer group';
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
    public function setName(string $name): CustomerGroupInterface
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
    public function setDefault(bool $default): CustomerGroupInterface
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isBusiness(): bool
    {
        return $this->business;
    }

    /**
     * @inheritdoc
     */
    public function setBusiness(bool $business): CustomerGroupInterface
    {
        $this->business = $business;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRegistration(): bool
    {
        return $this->registration;
    }

    /**
     * @inheritdoc
     */
    public function setRegistration(bool $registration): CustomerGroupInterface
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isQuoteAllowed(): bool
    {
        return $this->quoteAllowed;
    }

    /**
     * @inheritdoc
     */
    public function setQuoteAllowed(bool $allowed): CustomerGroupInterface
    {
        $this->quoteAllowed = $allowed;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isLoyalty(): bool
    {
        return $this->loyalty;
    }

    /**
     * @inheritdoc
     */
    public function setLoyalty(bool $enabled): CustomerGroupInterface
    {
        $this->loyalty = $enabled;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVatDisplayMode(): ?string
    {
        return $this->vatDisplayMode;
    }

    /**
     * @inheritdoc
     */
    public function setVatDisplayMode(string $mode = null): CustomerGroupInterface
    {
        $this->vatDisplayMode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }
}
