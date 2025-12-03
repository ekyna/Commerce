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

        $text = $subject->getReference() . ' - ' . $subject;

        $progress = $this->calculator->calculateProducedQuantity($object) / $object->getQuantity();

        if (self::contextHasGroup(['Gantt'], $context)) {
            return [
                'id'         => $object->getId(),
                'text'       => $text,
                'start_date' => $object->getStartAt()->format('Y-m-d 00:00'),
                'end_date'   => $object->getEndAt()->format('Y-m-d 23:59'),
                'progress'   => $progress,
            ];
        }

        return parent::normalize($object, $format, $context);
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
