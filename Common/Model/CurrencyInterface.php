<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CurrencyInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(string $name): CurrencyInterface;

    public function getCode(): ?string;

    public function setCode(string $code): CurrencyInterface;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): CurrencyInterface;
}
