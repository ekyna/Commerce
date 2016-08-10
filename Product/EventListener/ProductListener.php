<?php

namespace Ekyna\Component\Commerce\Product\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\EventListener\Handler;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Resource\Event\PersistenceEvent;

/**
 * Class ProductListener
 * @package Ekyna\Component\Commerce\Product\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductListener
{
    /**
     * @var Handler\HandlerFactory
     */
    private $factory;


    /**
     * Insert event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onInsert(PersistenceEvent $event)
    {
        $product = $this->getProductFromEvent($event);

        $handler = $this->getHandler($product);

        $handler->handleInsert($event);


        // TODO Timestampable behavior/listener
        $product
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
        $event->persistAndRecompute($product);
    }

    /**
     * Update event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $product = $this->getProductFromEvent($event);

        $handler = $this->getHandler($product);

        $handler->handleUpdate($event);


        // TODO Timestampable behavior/listener
        $product->setUpdatedAt(new \DateTime());
        $event->persistAndRecompute($product);
    }

    /**
     * Delete event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onDelete(PersistenceEvent $event)
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
            $this->factory = new Handler\HandlerFactory();
        }

        return $this->factory->getHandler($product);
    }

    /**
     * Returns the product from the event.
     *
     * @param PersistenceEvent $event
     *
     * @return ProductInterface
     */
    private function getProductFromEvent(PersistenceEvent $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected ProductInterface');
        }

        return $resource;
    }
}
