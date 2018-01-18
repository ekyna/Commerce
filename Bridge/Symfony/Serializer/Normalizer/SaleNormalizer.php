<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class SaleNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     */
    public function normalize($sale, $format = null, array $context = [])
    {
        $data = parent::normalize($sale, $format, $context);

        /** @var SaleInterface $sale */
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Default', $groups) || in_array('Search', $groups)) {
            $data = array_replace($data, [
                'number'     => $sale->getNumber(),
                'company'    => $sale->getCompany(),
                'email'      => $sale->getEmail(),
                'first_name' => $sale->getFirstName(),
                'last_name'  => $sale->getLastName(),
            ]);
        } elseif (in_array('Summary', $groups)) {
            $items = [];

            foreach ($sale->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            $data = array_replace($data, [
                'items'       => $items,
                'total'       => $sale->getGrandTotal(),
                'description' => $sale->getDescription(),
                'comment'     => $sale->getComment(),
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
        return $data instanceof SaleInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, SaleInterface::class);
    }
}