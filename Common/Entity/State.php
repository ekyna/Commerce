<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;

/**
 * Class State
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class State implements StateInterface
{
    protected ?int              $id      = null;
    protected ?CountryInterface $country = null;
    protected ?string           $name    = null;
    protected ?string           $code    = null;


    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->name ?: 'New state';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountry(): ?CountryInterface
    {
        return $this->country;
    }

    public function setCountry(CountryInterface $country): StateInterface
    {
        $this->country = $country;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): StateInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): StateInterface
    {
        $this->code = $code;

        return $this;
    }
}
