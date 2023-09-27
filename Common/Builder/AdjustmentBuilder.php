<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentDataInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function array_filter;
use function in_array;

/**
 * Class AdjustmentBuilder
 * @package Ekyna\Component\Commerce\Common\Builder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentBuilder implements AdjustmentBuilderInterface
{
    public function __construct(
        private readonly FactoryHelperInterface     $factoryHelper,
        private readonly PersistenceHelperInterface $persistenceHelper,
    ) {
    }

    /**
     * Builds the adjustments regarding the given data and type.
     *
     * @param string                              $type
     * @param AdjustableInterface                 $adjustable
     * @param array<int, AdjustmentDataInterface> $data
     * @param bool                                $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildAdjustments(
        string              $type,
        AdjustableInterface $adjustable,
        iterable            $data,
        bool                $persistence = false
    ): bool {
        AdjustmentTypes::isValidType($type);

        $change = false;

        // Generate adjustments
        $newAdjustments = [];
        foreach ($data as $datum) {
            if (!$datum instanceof AdjustmentDataInterface) {
                throw new UnexpectedTypeException($datum, AdjustmentDataInterface::class);
            }

            $adjustment = $this->factoryHelper->createAdjustmentFor($adjustable);
            $adjustment
                ->setType($type)
                ->setMode($datum->getMode())
                ->setDesignation($datum->getDesignation())
                ->setAmount($datum->getAmount())
                ->setImmutable($datum->isImmutable())
                ->setSource($datum->getSource());

            $newAdjustments[] = $adjustment;
        }

        // Current adjustments
        $oldAdjustments = $adjustable->getAdjustments($type);

        // Remove current adjustments that do not match any generated adjustments
        foreach ($oldAdjustments as $oldAdjustment) {
            // Skip non-immutable adjustment as they have been defined by the user.
            if (!$oldAdjustment->isImmutable()) {
                continue;
            }

            // Look for a corresponding adjustment
            foreach ($newAdjustments as $index => $newAdjustment) {
                if ($oldAdjustment->equals($newAdjustment)) {
                    // Remove the generated adjustment
                    unset($newAdjustments[$index]);
                    continue 2;
                }
            }

            // No matching generated adjustment found: remove the current.
            $adjustable->removeAdjustment($oldAdjustment);

            if ($persistence) {
                $this->persistenceHelper->remove($oldAdjustment, true);
            }

            $change = true;
        }

        // Adds the remaining generated adjustments
        foreach ($newAdjustments as $newAdjustment) {
            $adjustable->addAdjustment($newAdjustment);

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($newAdjustment, true);
            }

            $change = true;
        }

        return $change;
    }

    /**
     * @inheritDoc
     */
    public function makeAdjustmentsMutable(
        AdjustableInterface $adjustable,
        array               $types = [],
        bool                $persistence = false
    ): void {
        $adjustments = $this->filterAdjustments($adjustable, false, $types);

        foreach ($adjustments as $adjustment) {
            $adjustment
                ->setImmutable(false)
                ->setSource(null);
        }

        if (!$persistence) {
            return;
        }

        foreach ($adjustments as $adjustment) {
            $this->persistenceHelper->persistAndRecompute($adjustment, false);
        }
    }

    /**
     * @param AdjustableInterface $adjustable
     * @param array<string>       $types
     * @param bool                $persistence
     * @return void
     */
    public function clearMutableAdjustments(
        AdjustableInterface $adjustable,
        array               $types = [],
        bool                $persistence = false
    ): void {
        $adjustments = $this->filterAdjustments($adjustable, true, $types);

        foreach ($adjustments as $adjustment) {
            $adjustable->removeAdjustment($adjustment);
        }

        if (!$persistence) {
            return;
        }

        foreach ($adjustments as $adjustment) {
            $this->persistenceHelper->remove($adjustment, false);
        }
    }

    /**
     * @param AdjustableInterface $adjustable
     * @param bool                $mutable
     * @param array               $types
     * @return array<int, AdjustmentInterface>
     */
    private function filterAdjustments(
        AdjustableInterface $adjustable,
        bool                $mutable = true,
        array               $types = [],
    ): array {
        $adjustments = $adjustable->getAdjustments()->toArray();

        // Filter mutability
        $filter = static fn(AdjustmentInterface $a): bool => $mutable xor $a->isImmutable();
        /** @var array<AdjustmentInterface> $adjustments */
        $adjustments = array_filter($adjustments, $filter);

        // Filter by type
        if (!empty($types)) {
            $filter = static fn(AdjustmentInterface $a): bool => in_array($a->getType(), $types, true);
            $adjustments = array_filter($adjustments, $filter);
        }

        return $adjustments;
    }
}
