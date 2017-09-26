<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class ShipmentStateResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @todo    remove : Shipments do not need state resolution
 */
class ShipmentStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(StateSubjectInterface $subject)
    {
        if (!$subject instanceof ShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of ShipmentInterface.");
        }

        $changed = false;

        // TODO: Implement resolve() method.
        // State can be changed in the shipment form ... so we should not change it.

        return $changed;
    }
}
