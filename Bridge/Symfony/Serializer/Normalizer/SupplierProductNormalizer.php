<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class SupplierProductNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param SupplierProductInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup(['Default', 'SupplierProduct', 'Search'], $context)) {
            $supplier = $object->getSupplier();

            $data = array_replace($data, [
                'designation' => $object->getDesignation(),
                'reference'   => $object->getReference(),
                'net_price'   => $object->getNetPrice()->toFixed(5),
                'currency'    => $supplier->getCurrency()->getCode(),
                'supplier'    => [
                    'id'   => $supplier->getId(),
                    'name' => $supplier->getName(),
                ],
            ]);
        }

        return $data;
    }
}
