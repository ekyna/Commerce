<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Resource\Event\PersistenceEvent;

/**
 * Class VariableHandler
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableHandler extends AbstractHandler
{
    /**
     * @inheritDoc
     */
    public function handleInsert(PersistenceEvent $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->updater->updateVariableMinPrice($variable)) {
            $event->persistAndRecompute($variable);
        }
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(PersistenceEvent $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        $changeSet = $event->getChangeSet();

        if (array_key_exists('taxGroup', $changeSet)) {
            foreach ($variable->getVariants() as $variant) {
                if ($this->updater->updateVariantTaxGroup($variant)) {
                    $event->persistAndRecompute($variant);
                }
            }
        }
    }
}
