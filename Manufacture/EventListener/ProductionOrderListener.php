<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Commerce\Stock\Linker\ProductionOrderLinkerInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductionOrderListener
 * @package Ekyna\Component\Commerce\Manufacture\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property ResourceFactoryInterface<ProductionItemInterface>
 */
class ProductionOrderListener
{
    public function __construct(
        private readonly GeneratorInterface             $numberGenerator,
        private readonly SubjectHelperInterface         $subjectHelper,
        private readonly ResourceFactoryInterface       $itemFactory,
        private readonly ProductionOrderLinkerInterface $orderLinker,
        private readonly StockUnitAssignerInterface     $stockUnitAssigner,
        private readonly PersistenceHelperInterface     $persistenceHelper,
    ) {
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $order = $this->getPOFromEvent($event);

        $this->updateNumber($order);

        $this->updateSubject($order);

        $this->createItems($order);

        $this->persistenceHelper->persistAndRecompute($order, false);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $order = $this->getPOFromEvent($event);

        if (!$this->persistenceHelper->isChanged($order, ['state', 'quantity'])) {
            return;
        }

        $stateCs = $this->persistenceHelper->getChangeSet($order, 'state');

        if (POState::hasChangedToStockable($stateCs)) {
            $this->orderLinker->linkOrder($order);

            foreach ($order->getItems() as $item) {
                $this->stockUnitAssigner->assignProductionItem($item);
            }
        } elseif (POState::hasChangedFromStockable($stateCs)) {
            $this->orderLinker->unlinkOrder($order);

            foreach ($order->getItems() as $item) {
                $this->stockUnitAssigner->detachProductionItem($item);
            }
        } elseif (
            POState::isStockableState($order)
            && $this->persistenceHelper->isChanged($order, 'quantity')
        ) {
            foreach ($order->getItems() as $item) {
                $this->stockUnitAssigner->applyProductionItem($item);
            }
        } else {
            return;
        }

        $this->orderLinker->updateData($order);
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $order = $this->getPOFromEvent($event);

        if (!POState::isStockableState($order)) {
            return;
        }

        $this->orderLinker->unlinkOrder($order);

        foreach ($order->getItems() as $item) {
            $this->stockUnitAssigner->detachProductionItem($item);
        }
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $order = $this->getPOFromEvent($event);

        if (!POState::isStockableState($order)) {
            return;
        }

        throw new IllegalOperationException(
            'Production order with this state cannot be deleted'
        );
    }

    private function createItems(ProductionOrderInterface $order): void
    {
        $collection = new ArrayCollection();

        foreach ($order->getBom()->getComponents() as $component) {
            $subject = $this->subjectHelper->resolve($component);

            /** @var ProductionItemInterface $item */
            $item = $this->itemFactory->create();
            $this->subjectHelper->assign($item, $subject);

            $item->setDesignation((string)$subject);
            $item->setReference($subject->getReference());
            $item->setQuantity($component->getQuantity());
            $item->setProductionOrder($order);

            $this->persistenceHelper->persistAndRecompute($item, true);
        }

        // TODO Create items for operations

        $order->setItems($collection);
    }

    private function updateNumber(ProductionOrderInterface $order): void
    {
        if (!empty($order->getNumber())) {
            return;
        }

        $order->setNumber($this->numberGenerator->generate($order));
    }

    private function updateSubject(ProductionOrderInterface $order): void
    {
        $order->getSubjectIdentity()->copy($order->getBom()->getSubjectIdentity());
    }

    protected function getPOFromEvent(ResourceEventInterface $event): ProductionOrderInterface
    {
        $order = $event->getResource();

        if (!$order instanceof ProductionOrderInterface) {
            throw new UnexpectedTypeException($order, ProductionOrderInterface::class);
        }

        return $order;
    }
}
