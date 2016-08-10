<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Resource\Event\PersistenceEvent;

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
     * @param PersistenceEvent $event
     */
    public function handleInsert(PersistenceEvent $event);

    /**
     * Handles the product update event.
     *
     * @param PersistenceEvent $event
     */
    public function handleUpdate(PersistenceEvent $event);
}
