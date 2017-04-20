<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Payment\Model as Pay;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class PaymentTerm
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Pay\PaymentTermTranslationInterface translate($locale = null, $create = false)
 */
class PaymentTerm extends AbstractTranslatable implements Pay\PaymentTermInterface
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
     * @var int
     */
    protected $days;

    /**
     * @var boolean
     */
    protected $endOfMonth;

    /**
     * @var string
     */
    protected $trigger;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->days = 0;
        $this->endOfMonth = false;
        $this->trigger = Pay\PaymentTermTriggers::TRIGGER_INVOICED;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New payment term';
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
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
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @inheritDoc
     */
    public function setDays($days)
    {
        $this->days = (int)$days;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEndOfMonth()
    {
        return $this->endOfMonth;
    }

    /**
     * @inheritDoc
     */
    public function setEndOfMonth($endOfMonth)
    {
        $this->endOfMonth = (bool)$endOfMonth;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * @inheritDoc
     */
    public function setTrigger($trigger)
    {
        $this->trigger = $trigger;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->translate()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        $this->translate()->setDescription($description);

        return $this;
    }
}
