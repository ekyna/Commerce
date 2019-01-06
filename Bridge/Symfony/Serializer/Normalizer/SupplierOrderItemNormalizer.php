<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SupplierOrderItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemNormalizer extends AbstractResourceNormalizer
{
    /**
     * @var Formatter
     */
    protected $formatter;


    /**
     * Constructor.
     *
     * @param Formatter $formatter
     */
    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
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

            $data = array_replace($data, [
                'designation' => $item->getDesignation(),
                'net_price'   => $this->formatter->currency($item->getNetPrice(), $order->getCurrency()->getCode()),
                'ordered'     => $this->formatter->number($item->getQuantity()),
                'received'    => $this->formatter->number($received),
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
