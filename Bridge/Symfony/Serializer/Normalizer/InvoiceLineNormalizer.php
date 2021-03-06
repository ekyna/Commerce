<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class InvoiceLineNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param InvoiceLineInterface $line
     */
    public function normalize($line, $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('Summary', $context)) {
            $saleItem = $line->getSaleItem();

            $data = array_replace($data, [
                'designation' => $line->getDesignation(),
                'reference'   => $line->getReference(),
                'quantity'    => $line->getQuantity(),
                'total'       => $saleItem ? $saleItem->getTotalQuantity() : null,
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
        return $data instanceof InvoiceLineInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, InvoiceLineInterface::class);
    }
}
