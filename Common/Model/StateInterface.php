<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StateInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StateInterface extends ResourceInterface
{
    public function getCountry(): ?CountryInterface;

    public function setCountry(CountryInterface $country): StateInterface;

    public function getName(): ?string;

    public function setName(string $name): StateInterface;

    public function getCode(): ?string;

    public function setCode(string $code): StateInterface;
}
