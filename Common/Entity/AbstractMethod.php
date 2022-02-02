<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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

    protected ?string $name = null;
    /** @var Collection|Model\MessageInterface[] */
    protected Collection $messages;
    protected bool $enabled;
    protected bool $available;


    public function __construct()
    {
        parent::__construct();

        $this->messages = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->name ?: 'New method';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Model\MethodInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\MethodInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->translate()->getDescription();
    }

    public function setDescription(?string $description): Model\MethodInterface
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    public function hasMessages(): bool
    {
        return 0 < $this->messages->count();
    }

    public function hasMessage(Model\MessageInterface $message): bool
    {
        $this->validateMessageClass($message);

        return $this->messages->contains($message);
    }

    public function addMessage(Model\MessageInterface $message): Model\MethodInterface
    {
        $this->validateMessageClass($message);

        if ($this->hasMessage($message)) {
            return $this;
        }

        $message->setMethod($this);
        $this->messages->add($message);

        return $this;
    }

    public function removeMessage(Model\MessageInterface $message): Model\MethodInterface
    {
        $this->validateMessageClass($message);

        if (!$this->hasMessage($message)) {
            return $this;
        }

        $message->setMethod(null);
        $this->messages->removeElement($message);

        return $this;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function getMessageByState(string $state): ?Model\MessageInterface
    {
        foreach ($this->messages as $message) {
            if ($message->getState() === $state) {
                return $message;
            }
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): Model\MethodInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): Model\MethodInterface
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @throws UnexpectedTypeException
     */
    abstract protected function validateMessageClass(Model\MessageInterface $message): void;
}
