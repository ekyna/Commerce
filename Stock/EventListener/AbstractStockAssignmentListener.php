<?php

namespace Ekyna\Component\Commerce\Stock\EventListener;

/**
 * Class AbstractStockAssignmentListener
 * @package Ekyna\Component\Commerce\Stock\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockAssignmentListener
{
    public function onInsert()
    {
        // TODO Impact stock unit reserved quantity

        // Persist stock unit and schedule event
    }

    public function onUpdate()
    {
        // TODO Impact stock unit reserved quantity

        // Persist stock unit and schedule event
    }

    public function onDelete()
    {
        // TODO Impact stock unit reserved quantity

        // Persist stock unit and schedule event
    }
}
