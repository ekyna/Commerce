<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface IdentityInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface IdentityInterface
{
    public function setGender(?string $gender): IdentityInterface;

    public function getGender(): ?string;

    public function setFirstName(?string $firstName): IdentityInterface;

    public function getFirstName(): ?string;

    public function setLastName(?string $lastName): IdentityInterface;

    public function getLastName(): ?string;

    /**
     * Returns whether or not the identity is empty.
     */
    public function isIdentityEmpty(): bool;

    /**
     * Clears the identity.
     */
    public function clearIdentity(): IdentityInterface;
}
