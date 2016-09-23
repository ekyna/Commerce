<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Shipment\Entity\ShipmentMessage;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class ShipmentMethodRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodRepository extends TranslatableResourceRepository implements ShipmentMethodRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface $method */
        $method = parent::createNew();

        foreach (ShipmentStates::getNotifiableStates() as $state) {
            $message = new ShipmentMessage();
            $method->addMessage($message->setState($state));
        }

        return $method;
    }
}
