<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
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
    public function getName(): ?string;

    public function setName(string $name): MethodInterface;

    /**
     * Returns the title (translation alias).
     */
    public function getTitle(): ?string;

    /**
     * Sets the title (translation alias).
     */
    public function setTitle(?string $title): MethodInterface;

    /**
     * Returns the description (translation alias).
     */
    public function getDescription(): ?string;

    /**
     * Sets the description (translation alias).
     */
    public function setDescription(?string $description): MethodInterface;

    public function hasMessages(): bool;

    public function hasMessage(MessageInterface $message): bool;

    public function addMessage(MessageInterface $message): MethodInterface;

    public function removeMessage(MessageInterface $message): MethodInterface;

    /**
     * @return Collection|MessageInterface[]
     */
    public function getMessages(): Collection;

    public function getMessageByState(string $state): ?MessageInterface;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): MethodInterface;

    public function isAvailable(): bool;

    public function setAvailable(bool $available): MethodInterface;
}
