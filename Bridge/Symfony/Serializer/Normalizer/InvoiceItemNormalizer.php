<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceItemInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class InvoiceItemNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceItemNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param InvoiceItemInterface $item
     */
    public function normalize($item, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $data = array_replace($data, [
                'designation' => $item->getDesignation(),
                'reference'   => $item->getReference(),
                'quantity'    => $item->getQuantity(),
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
        return $data instanceof InvoiceItemInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, InvoiceItemInterface::class);
    }
}
