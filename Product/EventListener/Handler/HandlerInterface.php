<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Interface HandlerInterface
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface HandlerInterface
{
    const INSERT             = 'handleInsert';
    const UPDATE             = 'handleUpdate';
    const DELETE             = 'handleDelete';
    const STOCK_UNIT_CHANGE  = 'handleStockUnitChange';
    const CHILD_STOCK_CHANGE = 'handleChildStockChange';


    /**
     * Handles the product insert event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleInsert(ResourceEventInterface $event);

    /**
     * Handles the product update event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleUpdate(ResourceEventInterface $event);

    /**
     * Handles the product delete event.
     *
     * @param ResourceEventInterface $event
     */
    public function handleDelete(ResourceEventInterface $event);

    /**
     * Handles the stock unit change event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleStockUnitChange(ResourceEventInterface $event);

    /**
     * Handles the child stock change event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleChildStockChange(ResourceEventInterface $event);

    /**
     * Returns whether the handler supports the given product.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function supports(ProductInterface $product);
}
