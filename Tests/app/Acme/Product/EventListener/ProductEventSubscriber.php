<?php

namespace Acme\Product\EventListener;

use Acme\Product\Entity\Product;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductEventSubscriber
 * @package Acme\Product\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventSubscriber
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
     * Constructor.
     *
     * @param PersistenceHelperInterface   $persistenceHelper
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

        $changed = $this->stockUpdater->update($product);

        if ($changed) {
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

        $changed = false;

        $properties = ['stockMode', 'inStock', 'availableStock', 'virtualStock', 'estimatedDateOfArrival'];
        if ($this->persistenceHelper->isChanged($product, $properties)) {
            $changed = $this->stockUpdater->update($product);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Stock unit change event handler.
     *
     * @param SubjectStockUnitEvent $event
     */
    public function onStockUnitChange(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->stockUpdater->update($product);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Stock unit delete event handler.
     *
     * @param SubjectStockUnitEvent $event
     */
    public function onStockUnitRemoval(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->stockUpdater->update($product);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Returns the product from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Product
     */
    private function getProductFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof Product) {
            throw new InvalidArgumentException("Expected instance of " . Product::class);
        }

        return $resource;
    }
}
