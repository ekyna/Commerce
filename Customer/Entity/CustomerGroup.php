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
     * @var boolean
     */
    protected $default;

    /**
     * @var boolean
     */
    protected $business;

    /**
     * @var boolean
     */
    protected $registration;

    /**
     * @var boolean
     */
    protected $quoteAllowed;


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
     * Returns the id.
     *
     * @return int
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
    public function isBusiness()
    {
        return $this->business;
    }

    /**
     * @inheritdoc
     */
    public function setBusiness($business)
    {
        $this->business = (bool)$business;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRegistration()
    {
        return $this->registration;
    }

    /**
     * @inheritdoc
     */
    public function setRegistration($registration)
    {
        $this->registration = (bool)$registration;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isQuoteAllowed()
    {
        return $this->quoteAllowed;
    }

    /**
     * @inheritdoc
     */
    public function setQuoteAllowed($allowed)
    {
        $this->quoteAllowed = (bool)$allowed;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->translate()->getTitle();
    }
}
