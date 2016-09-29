<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

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
    public function handleInsert(ResourceEventInterface $event)
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
            $this->factory
                ->getPersistenceHelper()
                ->persistAndRecompute($variant);
        }
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        $persistenceHelper = $this->factory->getPersistenceHelper();

        // Generate variant designation if needed
        if (0 === strlen($variant->getDesignation()) && $this->updater->updateVariantDesignation($variant)) {
            $persistenceHelper->persistAndRecompute($variant);
        }

        // Update parent/variable minimum price if variant price has changed
        if ($persistenceHelper->isChanged($variant, 'netPrice')) {
            if (null === $variable = $variant->getParent()) {
                throw new RuntimeException("Variant's parent must be defined.");
            }

            if ($this->updater->updateVariableMinPrice($variable)) {
                $persistenceHelper->persistAndRecompute($variable);
            }
        }
    }
}
