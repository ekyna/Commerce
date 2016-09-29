<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

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
    public function handleInsert(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->updater->updateVariableMinPrice($variable)) {
            $this->factory
                ->getPersistenceHelper()
                ->persistAndRecompute($variable);
        }
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        $persistenceHelper = $this->factory->getPersistenceHelper();

        if ($persistenceHelper->isChanged($variable, 'taxGroup')) {
            foreach ($variable->getVariants() as $variant) {
                if ($this->updater->updateVariantTaxGroup($variant)) {
                    $persistenceHelper->persistAndRecompute($variable);
                }
            }
        }
    }
}
