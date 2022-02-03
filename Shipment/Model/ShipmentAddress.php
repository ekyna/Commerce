<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;

/**
 * Class ShipmentAddress
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddress extends AbstractAddress
{
    protected ?string $information = null;

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): ShipmentAddress
    {
        $this->information = $information;

        return $this;
    }
}
