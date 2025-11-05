<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\EventListener;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductionItemListener
 * @package Ekyna\Component\Commerce\Manufacture\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionItemListener
{
    public function __construct(
        private readonly StockUnitAssignerInterface $stockAssigner,
        private readonly PersistenceHelperInterface $persistenceHelper,
    ) {
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $item = $this->getItemFromEvent($event);

        // If order is in stockable state
        if (POState::isStockableState($item->getProductionOrder()->getState())) {
            // Create stock assignments
            $this->stockAssigner->assignProductionItem($item);

            return;
        }

        // Production order state is 'new' or 'canceled', detach component
        $this->stockAssigner->detachProductionItem($item);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $item = $this->getItemFromEvent($event);

        // TODO Disallow subject change
        // TODO Sync with subject (designation, reference, etc)

        if (!$this->persistenceHelper->isChanged($item, 'quantity')) {
            return;
        }

        $order = $item->getProductionOrder();
        if (!POState::isStockableState($order)) {
            return;
        }

        $stateCs = $this->persistenceHelper->getChangeSet($order, 'state');
        if (!(
            $this->persistenceHelper->isChanged($order, 'quantity')
            || POState::hasChangedToStockable($stateCs)
        )) {
            return;
        }

        $this->stockAssigner->applyProductionItem($item);
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        // TODO Prevent removal of assigned components

        $component = $this->getItemFromEvent($event);

        if ($component->hasStockAssignments()) {
            $this->stockAssigner->detachProductionItem($component);
        }
    }

    protected function getItemFromEvent(ResourceEventInterface $event): ProductionItemInterface
    {
        $component = $event->getResource();

        if (!$component instanceof ProductionItemInterface) {
            throw new UnexpectedTypeException($component, ProductionItemInterface::class);
        }

        return $component;
    }
}
