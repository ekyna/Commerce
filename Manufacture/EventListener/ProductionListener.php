<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\EventListener;

use DateTime;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionOrderCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Commerce\Stock\Linker\ProductionOrderLinkerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductionListener
 * @package Ekyna\Component\Commerce\Manufacture\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionListener
{
    public function __construct(
        private readonly ProductionOrderLinkerInterface $linker,
        private readonly StockUnitAssignerInterface     $assigner,
        private readonly ProductionOrderCalculator      $orderCalculator,
        private readonly PersistenceHelperInterface     $persistenceHelper,
    ) {
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $production = $this->getProductionFromEvent($event);

        $this->generateNumber($production);

        $this->assigner->assignProduction($production);

        $this->linker->linkProduction($production);

        $order = $production->getProductionOrder();

        $this->linker->updateData($order);

        $this->updateOrderStateAndDate($order);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $production = $this->getProductionFromEvent($event);

        if (!$this->persistenceHelper->isChanged($production, 'quantity')) {
            return;
        }

        $this->assigner->applyProduction($production);

        $this->linker->applyProduction($production);

        $order = $production->getProductionOrder();

        $this->linker->updateData($order);

        $this->updateOrderStateAndDate($order);
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $production = $this->getProductionFromEvent($event);

        $this->assigner->detachProduction($production);

        $this->linker->unlinkProduction($production);

        $order = $production->getProductionOrder();

        $this->linker->updateData($order);

        $this->updateOrderStateAndDate($order);
    }

    private function generateNumber(ProductionInterface $production): void
    {
        if (!empty($production->getNumber())) {
            return;
        }

        $count = 0;
        foreach ($production->getProductionOrder()->getProductions() as $p) {
            if ($p === $production) {
                continue;
            }
            $count++;
        }

        $production->setNumber(++$count);

        $this->persistenceHelper->persistAndRecompute($production, false);
    }

    private function updateOrderStateAndDate(ProductionOrderInterface $order): void
    {
        $produced = $this->orderCalculator->calculateProducedQuantity($order);
        if ($produced != $order->getQuantity()) {
            if ($order->getState() === POState::SCHEDULED) {
                return;
            }

            $order->setState(POState::SCHEDULED);

            $this->persistenceHelper->persistAndRecompute($order, false);

            return;
        }

        $changed = false;
        if ($order->getState() !== POState::DONE) {
            $order->setState(POState::DONE);
            $changed = true;
        }

        $date = new DateTime('2000-01-01');
        foreach ($order->getProductions() as $production) {
            if ($date < $production->getCreatedAt()) {
                $date = $production->getCreatedAt();
            }
        }

        if ($order->getEndAt() != $date) {
            $order->setEndAt($date);
            $changed = true;
        }

        if (!$changed) {
            return;
        }

        $this->persistenceHelper->persistAndRecompute($order, false);
    }

    private function getProductionFromEvent(ResourceEventInterface $event): ProductionInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductionInterface) {
            throw new UnexpectedTypeException($resource, ProductionInterface::class);
        }

        return $resource;
    }
}
