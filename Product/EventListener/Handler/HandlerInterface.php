<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Interface HandlerInterface
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface HandlerInterface
{
    /**
     * Handles the product insert event.
     *
     * @param ResourceEventInterface $event
     */
    public function handleInsert(ResourceEventInterface $event);

    /**
     * Handles the product update event.
     *
     * @param ResourceEventInterface $event
     */
    public function handleUpdate(ResourceEventInterface $event);
}
