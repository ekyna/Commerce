<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class ShipmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentNormalizer extends ResourceNormalizer
{
    private ShipmentAddressResolverInterface $addressResolver;
    private WeightCalculatorInterface        $weightCalculator;

    public function __construct(
        ShipmentAddressResolverInterface $addressResolver,
        WeightCalculatorInterface        $weightCalculator
    ) {
        $this->addressResolver = $addressResolver;
        $this->weightCalculator = $weightCalculator;
    }

    /**
     * @inheritDoc
     *
     * @param ShipmentInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup(['Default', 'OrderShipment'], $context)) {
            $sale = $object->getSale();

            $data = array_replace($data, [
                'number'          => $object->getNumber(),
                'company'         => $sale->getCompany(),
                'email'           => $sale->getEmail(),
                'first_name'      => $sale->getFirstName(),
                'last_name'       => $sale->getLastName(),
                'type'            => $object->isReturn() ? 'return' : 'shipment',
                'method'          => $object->getMethod()->getName(),
                'tracking_number' => $object->getTrackingNumber(),
                'description'     => $object->getDescription(),
            ]);
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $items = [];
            $parcels = [];

            foreach ($object->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }
            foreach ($object->getParcels() as $parcel) {
                $parcels[] = $this->normalizeObject($parcel, $format, $context);
            }

            $senderAddress = $this->addressResolver->resolveSenderAddress($object);
            $senderAddress = $this->normalizeObject($senderAddress, $format, $context);

            $receiverAddress = $this->addressResolver->resolveReceiverAddress($object);
            $receiverAddress = $this->normalizeObject($receiverAddress, $format, $context);

            if (null === $weight = $object->getWeight()) {
                $weight = $this->weightCalculator->calculateShipment($object);
            }

            $data = array_replace($data, [
                'number'           => $object->getNumber(),
                'weight'           => $weight->toFixed(3),
                'tracking_number'  => $object->getTrackingNumber(),
                'valorization'     => $object->getValorization(),
                'sender_address'   => $senderAddress,
                'receiver_address' => $receiverAddress,
                'items'            => $items,
                'parcels'          => $parcels,
                'description'      => $object->getDescription(),
            ]);
        }

        return $data;
    }
}
