<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;

/**
 * Class Currency
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Currency implements CurrencyInterface
{
    protected ?int    $id      = null;
    protected ?string $name    = null;
    protected ?string $code    = null;
    protected bool    $enabled = true;
    protected bool    $default = false;


    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->name ?: 'New currency';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): CurrencyInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): CurrencyInterface
    {
        $this->code = $code;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): CurrencyInterface
    {
        $this->enabled = $enabled;

        return $this;
    }
}
