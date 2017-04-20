<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class StockAdjustmentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentNormalizer extends ResourceNormalizer
{
    use FormatterAwareTrait;

    /**
     * @inheritDoc
     *
     * @param StockAdjustmentInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        if ($this->contextHasGroup('StockView', $context)) {
            $formatter = $this->getFormatter();

            $data = array_replace($data, [
                'quantity'   => $formatter->number($object->getQuantity()),
                'reason'     => $object->getReason(),
                'note'       => $object->getNote(),
                'created_at' => $formatter->date($object->getCreatedAt()),
            ]);
        }

        return $data;
    }
}
