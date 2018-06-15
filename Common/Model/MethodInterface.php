<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface MethodInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method MethodTranslationInterface translate($locale = null, $create = false)
 */
interface MethodInterface extends ResourceModel\TranslatableInterface, ResourceModel\SortableInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|MethodInterface
     */
    public function setName($name);

    /**
     * Returns the title (translation alias).
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title (translation alias).
     *
     * @param string $title
     *
     * @return $this|MethodInterface
     */
    public function setTitle($title);

    /**
     * Returns the description (translation alias).
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description (translation alias).
     *
     * @param string $description
     *
     * @return $this|MethodInterface
     */
    public function setDescription($description);

    /**
     * Returns whether the method has at least one message or not.
     *
     * @return bool
     */
    public function hasMessages();

    /**
     * Returns whether the method has the message or not.
     *
     * @param MessageInterface $message
     *
     * @return bool
     */
    public function hasMessage(MessageInterface $message);

    /**
     * Adds the message.
     *
     * @param MessageInterface $message
     *
     * @return $this|MethodInterface
     */
    public function addMessage(MessageInterface $message);

    /**
     * Removes the message.
     *
     * @param MessageInterface $message
     *
     * @return $this|MethodInterface
     */
    public function removeMessage(MessageInterface $message);

    /**
     * Returns the messages.
     *
     * @return \Doctrine\Common\Collections\Collection|MessageInterface[]
     */
    public function getMessages();

    /**
     * Returns the messages for the given state.
     *
     * @param string $state
     *
     * @return MessageInterface|null
     */
    public function getMessageByState($state);

    /**
     * Returns whether the method is enabled or not.
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * Sets whether the method is enabled or not.
     *
     * @param boolean $enabled
     *
     * @return $this|MethodInterface
     */
    public function setEnabled($enabled);

    /**
     * Returns whether the method is available or not.
     *
     * @return boolean
     */
    public function isAvailable();

    /**
     * Sets whether the method is available or not.
     *
     * @param boolean $available
     *
     * @return $this|MethodInterface
     */
    public function setAvailable($available);
}
