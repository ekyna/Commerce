<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;

/**
 * Class ShipmentAddress
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddress extends AbstractAddress
{
    /**
     * @var string
     */
    protected $information;


    /**
     * Returns the information.
     *
     * @return string|null
     */
    public function getInformation(): ?string
    {
        return $this->information;
    }

    /**
     * Sets the information.
     *
     * @param string|null $information
     *
     * @return $this|ShipmentAddress
     */
    public function setInformation(string $information = null): ShipmentAddress
    {
        $this->information = $information;

        return $this;
    }
}
