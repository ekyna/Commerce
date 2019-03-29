<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SupplierOrderItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemNormalizer extends AbstractResourceNormalizer
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
     * @inheritdoc
     *
     * @param SupplierOrderItemInterface $item
     */
    public function normalize($item, $format = null, array $context = [])
    {
        $data = parent::normalize($item, $format, $context);

        if ($this->contextHasGroup('Summary', $context)) {
            $order = $item->getOrder();

            $received = 0;
            foreach ($order->getDeliveries() as $delivery) {
                foreach ($delivery->getItems() as $di) {
                    if ($di->getOrderItem() === $item) {
                        $received += $di->getQuantity();
                    }
                }
            }

            $formatter = $this->getFormatter();

            $data = array_replace($data, [
                'designation' => $item->getDesignation(),
                'net_price'   => $formatter->currency($item->getNetPrice(), $order->getCurrency()->getCode()),
                'ordered'     => $formatter->number($item->getQuantity()),
                'received'    => $formatter->number($received),
            ]);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SupplierOrderItemInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, SupplierOrderItemInterface::class);
    }
}
