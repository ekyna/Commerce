<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Transformer\ArrayToAddressTransformer;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class ShipmentAddressResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class ShipmentAddressResolver implements ShipmentAddressResolverInterface
{
    protected ArrayToAddressTransformer $transformer;

    public function __construct(ArrayToAddressTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function getCountryRepository(): CountryRepositoryInterface
    {
        return $this->transformer->getCountryRepository();
    }

    public function resolveSenderAddress(ShipmentInterface $shipment, bool $ignoreRelay = false): AddressInterface
    {
        if (!$ignoreRelay && $shipment->isReturn() && ($address = $shipment->getRelayPoint())) {
            return $address;
        }

        if (!empty($address = $shipment->getSenderAddress())) {
            return $this->transformer->transformArray($address);
        }

        if ($shipment->isReturn()) {
            return $this->getSaleDeliveryAddress($shipment);
        }

        return $this->getCompanyAddress();
    }

    public function resolveReceiverAddress(ShipmentInterface $shipment, bool $ignoreRelay = false): AddressInterface
    {
        if (!$ignoreRelay && !$shipment->isReturn() && ($address = $shipment->getRelayPoint())) {
            return $address;
        }

        if (!empty($address = $shipment->getReceiverAddress())) {
            return $this->transformer->transformArray($address);
        }

        if ($shipment->isReturn()) {
            return $this->getCompanyAddress();
        }

        return $this->getSaleDeliveryAddress($shipment);
    }

    /**
     * Returns the company address.
     */
    abstract protected function getCompanyAddress(): AddressInterface;

    /**
     * Returns the delivery address of the shipment's sale.
     *
     * @throws LogicException
     */
    private function getSaleDeliveryAddress(ShipmentInterface $shipment): AddressInterface
    {
        if (null === $sale = $shipment->getSale()) {
            throw new LogicException('Shipment\'s sale must be set at this point.');
        }

        return $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress();
    }
}
