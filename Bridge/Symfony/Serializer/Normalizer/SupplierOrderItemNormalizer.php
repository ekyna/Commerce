<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class SupplierOrderItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemNormalizer extends ResourceNormalizer
{
    use FormatterAwareTrait;


    /**
     * Constructor.
     *
     * @param FormatterFactory $formatterFactory
     */
    public function __construct(FormatterFactory $formatterFactory)
    {
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @inheritDoc
     *
     * @param SupplierOrderItemInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup('Summary', $context)) {
            $order = $object->getOrder();

            $received = 0;
            foreach ($order->getDeliveries() as $delivery) {
                foreach ($delivery->getItems() as $di) {
                    if ($di->getOrderItem() === $object) {
                        $received += $di->getQuantity();
                    }
                }
            }

            $formatter = $this->getFormatter();

            $data = array_replace($data, [
                'designation' => $object->getDesignation(),
                'net_price'   => $formatter->currency($object->getNetPrice(), $order->getCurrency()->getCode()),
                'ordered'     => $formatter->number($object->getQuantity()),
                'received'    => $formatter->number($received),
            ]);
        }

        return $data;
    }
}
