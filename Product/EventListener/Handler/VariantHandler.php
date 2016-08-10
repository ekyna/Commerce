<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Resource\Event\PersistenceEvent;

/**
 * Class VariantHandler
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantHandler extends AbstractHandler
{
    /**
     * @inheritDoc
     */
    public function handleInsert(PersistenceEvent $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        $changed = false;

        // Generate variant designation if needed
        if (0 === strlen($variant->getDesignation())) {
            $changed = $this->updater->updateVariantDesignation($variant);
        }

        // Set tax group regarding to parent/variable if needed
        $changed = $this->updater->updateVariantTaxGroup($variant) || $changed;

        if ($changed) {
            $event->persistAndRecompute($variant);
        }
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(PersistenceEvent $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        $changeSet = $event->getChangeSet();

        // Generate variant designation if needed
        if (0 === strlen($variant->getDesignation()) && $this->updater->updateVariantDesignation($variant)) {
            $event->persistAndRecompute($variant);
        }

        // Update parent/variable minimum price if variant price has changed
        if (array_key_exists('netPrice', $changeSet)) {
            if (null === $variable = $variant->getParent()) {
                throw new RuntimeException("Variant's parent must be defined.");
            }

            if ($this->updater->updateVariableMinPrice($variable)) {
                $event->persistAndRecompute($variable);
            }
        }
    }
}
