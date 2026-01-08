<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use DateTime;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionOrderCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ObjectToPopulateTrait;

use function array_replace;

/**
 * Class ProductionOrderNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderNormalizer extends ResourceNormalizer
{
    use ObjectToPopulateTrait;

    public function __construct(
        private readonly SubjectHelperInterface    $helper,
        private readonly ProductionOrderCalculator $calculator,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param ProductionOrderInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $subject = $this->helper->resolve($object);

        $data = [
            'id'   => $object->getId(),
            'text' => $subject->getReference() . ' - ' . $subject,
        ];

        $produced = $this->calculator->calculateProducedQuantity($object);

        if (self::contextHasGroup(['Gantt'], $context)) {
            return array_replace($data, [
                'start_date' => $object->getStartAt()->format('Y-m-d 00:00'),
                'end_date'   => $object->getEndAt()->format('Y-m-d 23:59'),
                'progress'   => $produced / $object->getQuantity(),
            ]);
        }

        if (self::contextHasGroup(['Summary'], $context)) {
            $items = [];
            foreach ($object->getItems() as $item) {
                $items[] = $this->normalizeObject($item, $format, $context);
            }

            return array_replace($data, [
                'start_date' => $object->getStartAt()->format('Y-m-d'),
                'end_date'   => $object->getEndAt()->format('Y-m-d'),
                'produced' => $produced,
                'quantity' => $object->getQuantity(),
                'items' => $items,
            ]);
        }

        return $data;
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        if (self::contextHasGroup(['Gantt'], $context)) {
            $order = $this->extractObjectToPopulate($type, $context);
            if (!$order instanceof ProductionOrderInterface) {
                throw new InvalidArgumentException();
            }

            $order->setStartAt(new DateTime($data['start_date']));
            $order->setEndAt(new DateTime($data['end_date']));

            return $order;
        }

        return parent::denormalize($data, $type, $format, $context);
    }
}
