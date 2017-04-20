<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory;

use Ekyna\Component\Commerce\Shipment\Entity\ShipmentMessage;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\TranslatableFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ShipmentMethodFactory
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodFactory extends TranslatableFactory
{
    /**
     * @inheritDoc
     */
    public function create(): ResourceInterface
    {
        /** @var ShipmentMethodInterface $method */
        $method = parent::create();

        foreach (ShipmentStates::getNotifiableStates() as $state) {
            $message = new ShipmentMessage();
            $method->addMessage($message->setState($state));
        }

        return $method;
    }
}
