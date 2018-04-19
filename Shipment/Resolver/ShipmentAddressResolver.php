<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Transformer\ShipmentAddressTransformer;

/**
 * Class ShipmentAddressResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class ShipmentAddressResolver implements ShipmentAddressResolverInterface
{
    /**
     * @var ShipmentAddressTransformer
     */
    protected $transformer;


    /**
     * Constructor.
     *
     * @param ShipmentAddressTransformer $transformer
     */
    public function __construct(ShipmentAddressTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function getCountryRepository()
    {
        return $this->transformer->getCountryRepository();
    }

    /**
     * @inheritDoc
     */
    public function resolveSenderAddress(ShipmentInterface $shipment, bool $ignoreRelay = false)
    {
        if (!$ignoreRelay && $shipment->isReturn() && null !== $address = $shipment->getRelayPoint()) {
            return $address;
        }

        if (!empty($address = $shipment->getSenderAddress())) {
            return $this->transformer->transform($address);
        }

        if ($shipment->isReturn()) {
            return $this->getSaleDeliveryAddress($shipment);
        }

        return $this->getCompanyAddress();
    }

    /**
     * @inheritDoc
     */
    public function resolveReceiverAddress(ShipmentInterface $shipment, bool $ignoreRelay = false)
    {
        if (!$ignoreRelay && !$shipment->isReturn() && null !== $address = $shipment->getRelayPoint()) {
            return $address;
        }

        if (!empty($address = $shipment->getReceiverAddress())) {
            return $this->transformer->transform($address);
        }

        if ($shipment->isReturn()) {
            return $this->getCompanyAddress();
        }

        return $this->getSaleDeliveryAddress($shipment);
    }

    /**
     * Returns the company address.
     *
     * @return AddressInterface $address
     */
    abstract protected function getCompanyAddress();

    /**
     * Returns the delivery address of the shipment's sale.
     *
     * @param ShipmentInterface $shipment
     *
     * @return AddressInterface
     * @throws LogicException
     */
    private function getSaleDeliveryAddress(ShipmentInterface $shipment)
    {
        if (null === $sale = $shipment->getSale()) {
            throw new LogicException("Shipment's sale must be set at this point.");
        }

        return $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress();
    }
}
