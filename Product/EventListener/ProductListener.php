<?php

namespace Ekyna\Component\Commerce\Product\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\EventListener\Handler;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductListener
 * @package Ekyna\Component\Commerce\Product\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockSubjectUpdaterInterface
     */
    protected $stockUpdater;

    /**
     * @var Handler\HandlerFactory
     */
    private $factory;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockSubjectUpdaterInterface $stockUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockSubjectUpdaterInterface $stockUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUpdater = $stockUpdater;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $handler = $this->getHandler($product);

        $changed = $handler->handleInsert($event);

        // TODO bundled or variable stock, stock state and eda
        $changed = $this->stockUpdater->update($product) || $changed;

        // TODO Timestampable behavior/listener
        $product
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if ($changed || true) { // TODO
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $handler = $this->getHandler($product);

        $changed = $handler->handleUpdate($event);

        // TODO bundled or variable stock, stock state and eda
        // TODO move this in stock handler and use inheritance
        if ($this->persistenceHelper->isChanged($product, ['inStock', 'orderedStock', 'estimatedDateOfArrival'])) {
            $this->stockUpdater->updateStockState($product);
        }

        // TODO Timestampable behavior/listener
        $product->setUpdatedAt(new \DateTime());

        if ($changed || true) { // TODO
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        //$product = $this->getProductFromEvent($event);
    }

    /**
     * Stock unit change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStockUnitChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $stockUnitChangeSet = $event->getData('stock_unit_change_set');

        $changed = false;

        // By stock unit change set
        if ($event->hasData('stock_unit_change_set')) {
            if (in_array('inStock', $stockUnitChangeSet)) {
                $changed = $this->stockUpdater->updateInStock($product);
            }
            if (in_array('orderedStock', $stockUnitChangeSet)) {
                $changed = $this->stockUpdater->updateOrderedStock($product) || $changed;
            }
            if ($changed || in_array('estimatedDateOfArrival', $stockUnitChangeSet)) {
                $changed = $this->stockUpdater->updateEstimatedDateOfArrival($product) || $changed;
            }
        } else { // Whole update
            $changed = $this->stockUpdater->updateInStock($product);
            $changed = $this->stockUpdater->updateOrderedStock($product) || $changed;
            $changed = $this->stockUpdater->updateEstimatedDateOfArrival($product) || $changed;
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Returns the product handler regarding to product type.
     *
     * @param ProductInterface $product
     *
     * @return Handler\HandlerInterface
     */
    protected function getHandler(ProductInterface $product)
    {
        if (null === $this->factory) {
            $this->factory = new Handler\HandlerFactory($this->persistenceHelper);
        }

        return $this->factory->getHandler($product);
    }

    /**
     * Returns the product from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ProductInterface
     */
    private function getProductFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected ProductInterface');
        }

        return $resource;
    }
}
