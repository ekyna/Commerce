<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;

/**
 * Interface AddressResolverAwareInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AddressResolverAwareInterface
{
    public function setAddressResolver(ShipmentAddressResolverInterface $resolver): void;

    public function getAddressResolver(): ShipmentAddressResolverInterface;
}
