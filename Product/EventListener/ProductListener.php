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
    protected $stockStateUpdater;

    /**
     * @var Handler\HandlerFactory
     */
    private $factory;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockSubjectUpdaterInterface $stockStateUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockSubjectUpdaterInterface $stockStateUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockStateUpdater = $stockStateUpdater;
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

        $changed = false;

        $changed = $handler->handleInsert($event) || $changed;

        $changed = $this->stockStateUpdater->update($product) || $changed;

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

        $changed = false;

        $handler->handleUpdate($event) || $changed;

        $this->stockStateUpdater->update($product) || $changed;

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

        if ($this->stockStateUpdater->update($product)) {
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
