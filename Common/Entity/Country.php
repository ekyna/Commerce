<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;

/**
 * Class Country
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Country implements CountryInterface
{
    protected ?int   $id      = null;
    protected string $name;
    protected string $code;
    protected bool   $enabled = true;
    protected bool   $default = false;
    /** @var Collection|StateInterface[] */
    protected Collection $states;


    public function __construct()
    {
        $this->states = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->name ?: 'New country';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): CountryInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): CountryInterface
    {
        $this->code = $code;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): CountryInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getStates(): Collection
    {
        return $this->states;
    }

    public function hasState(StateInterface $state): bool
    {
        return $this->states->contains($state);
    }

    public function addState(StateInterface $state): CountryInterface
    {
        if ($this->hasState($state)) {
            return $this;
        }

        $state->setCountry($this);
        $this->states->add($state);

        return $this;
    }

    public function removeState(StateInterface $state): CountryInterface
    {
        if (!$this->hasState($state)) {
            return $this;
        }

        $state->setCountry(null);
        $this->states->removeElement($state);

        return $this;
    }

    public function setStates(Collection $states): CountryInterface
    {
        $this->states = $states;

        return $this;
    }
}
