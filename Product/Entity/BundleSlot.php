<?php

namespace Ekyna\Component\Commerce\Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Product\Model as Model;
use Ekyna\Component\Commerce\Product\Model\BundleSlotInterface;

/**
 * Class BundleSlot
 * @package Ekyna\Component\Commerce\Product\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlot implements BundleSlotInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\ProductInterface
     */
    protected $bundle;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var ArrayCollection|Model\BundleChoiceInterface[]
     */
    protected $choices;

    /**
     * @var integer
     */
    protected $position;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    /**
     * Clones the bundle slot.
     */
    public function __clone()
    {
        $choices = $this->choices;
        $this->choices = new ArrayCollection();
        foreach ($choices as $choice) {
            $this->addChoice(clone $choice);
        }
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
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @inheritdoc
     */
    public function setBundle(Model\ProductInterface $bundle = null)
    {
        $this->bundle = $bundle;

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

    /**
     * @inheritdoc
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @inheritdoc
     */
    public function hasChoice(Model\BundleChoiceInterface $choice)
    {
        return $this->choices->contains($choice);
    }

    /**
     * @inheritdoc
     */
    public function addChoice(Model\BundleChoiceInterface $choice)
    {
        if (!$this->hasChoice($choice)) {
            $choice->setSlot($this);
            $this->choices->add($choice);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChoice(Model\BundleChoiceInterface $choice)
    {
        if ($this->hasChoice($choice)) {
            $choice->setSlot(null);
            $this->choices->removeElement($choice);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
