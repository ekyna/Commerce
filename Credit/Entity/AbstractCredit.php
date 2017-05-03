<?php

namespace Ekyna\Component\Commerce\Credit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Credit\Model as Credit;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractCredit
 * @package Ekyna\Component\Commerce\Credit\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCredit implements Credit\CreditInterface
{
    use Common\NumberSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var ArrayCollection|Credit\CreditItemInterface[]
     */
    protected $items;

    /**
     * @var string
     */
    protected $description;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getNumber();
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
    public function hasItems()
    {
        return 0 < $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Credit\CreditItemInterface $item)
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Credit\CreditItemInterface $item)
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setCredit($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Credit\CreditItemInterface $item)
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setCredit(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
