<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CountryInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CountryInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(string $name): CountryInterface;

    public function getCode(): ?string;

    public function setCode(string $code): CountryInterface;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): CountryInterface;

    /**
     * @return Collection|StateInterface[]
     */
    public function getStates(): Collection;

    public function hasState(StateInterface $state): bool;

    public function addState(StateInterface $state): CountryInterface;

    public function removeState(StateInterface $state): CountryInterface;

    public function setStates(Collection $states): CountryInterface;
}
