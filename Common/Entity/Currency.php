<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class Currency
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Currency extends AbstractResource implements CurrencyInterface
{
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
