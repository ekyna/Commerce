<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SupplierNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param SupplierInterface $supplier
     */
    public function normalize($supplier, $format = null, array $context = [])
    {
        $data = parent::normalize($supplier, $format, $context);

        if ($this->contextHasGroup('Search', $context)) {
            $data = array_replace($data, [
                'name'        => $supplier->getName(),
                'description' => $supplier->getDescription(),
            ]);
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $data = array_replace($data, [
                'description' => $supplier->getDescription(),
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
        return $data instanceof SupplierInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, SupplierInterface::class);
    }
}
