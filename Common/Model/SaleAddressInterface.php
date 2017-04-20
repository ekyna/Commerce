<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SaleAddressInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAddressInterface extends AddressInterface, ResourceInterface
{
    public function getSale(): ?SaleInterface;

    public function getInformation(): ?string;

    public function setInformation(?string $information): SaleAddressInterface;
}
