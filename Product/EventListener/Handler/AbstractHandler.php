<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Updater\ProductUpdater;
use Ekyna\Component\Commerce\Product\Updater\ProductUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class AbstractHandler
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var HandlerFactory
     */
    protected $factory;

    /**
     * @var ProductUpdaterInterface
     */
    protected $updater;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->updater = new ProductUpdater();
    }

    /**
     * Sets the factory.
     *
     * @param HandlerFactory $factory
     */
    public function setFactory(HandlerFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Returns the product from the event.
     *
     * @param ResourceEventInterface $event
     * @param string $type
     *
     * @return ProductInterface
     */
    protected function getProductFromEvent(ResourceEventInterface $event, $type = null)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new InvalidArgumentException("Expected ProductInterface");
        }

        if (null !== $type && $type != $resource->getType()) {
            throw new InvalidArgumentException("Expected product with type '$type'.");
        }

        return $resource;
    }
}
