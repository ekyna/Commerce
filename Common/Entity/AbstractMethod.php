<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class AbstractMethod
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\MethodTranslationInterface translate($locale = null, $create = false)
 */
abstract class AbstractMethod extends RM\AbstractTranslatable implements Model\MethodInterface
{
    use RM\SortableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection|Model\MessageInterface[]
     */
    protected $messages;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var bool
     */
    protected $available;

    /**
     * @var integer
     */
    protected $position;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->messages = new ArrayCollection();
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
    public function getTitle()
    {
        return $this->translate()->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasMessages()
    {
        return 0 < $this->messages->count();
    }

    /**
     * @inheritdoc
     */
    public function hasMessage(Model\MessageInterface $message)
    {
        $this->validateMessageClass($message);

        return $this->messages->contains($message);
    }

    /**
     * @inheritdoc
     */
    public function addMessage(Model\MessageInterface $message)
    {
        $this->validateMessageClass($message);

        if (!$this->hasMessage($message)) {
            $message->setMethod($this);
            $this->messages->add($message);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeMessage(Model\MessageInterface $message)
    {
        $this->validateMessageClass($message);

        if ($this->hasMessage($message)) {
            $message->setMethod(null);
            $this->messages->removeElement($message);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return $this->available;
    }

    /**
     * @inheritdoc
     */
    public function setAvailable($available)
    {
        $this->available = (bool) $available;

        return $this;
    }

    /**
     * Validates the message class.
     *
     * @param Model\MessageInterface $message
     *
     * @throws \Ekyna\Component\Commerce\Exception\InvalidArgumentException
     */
    abstract protected function validateMessageClass(Model\MessageInterface $message);
}
