<?php

namespace Ekyna\Component\Commerce\Product\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\EventListener\Handler;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
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
     * @var Handler\HandlerFactory
     */
    private $factory;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
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

        $handler->handleInsert($event);


        // TODO Timestampable behavior/listener
        $product
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->persistenceHelper->persistAndRecompute($product);
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

        $handler->handleUpdate($event);


        // TODO Timestampable behavior/listener
        $product->setUpdatedAt(new \DateTime());

        $this->persistenceHelper->persistAndRecompute($product);
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
