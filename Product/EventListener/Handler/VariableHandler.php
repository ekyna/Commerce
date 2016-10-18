<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Commerce\Product\Updater\VariableUpdater;
use Ekyna\Component\Commerce\Product\Updater\VariantUpdater;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class VariableHandler
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableHandler extends AbstractHandler
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var VariantUpdater
     */
    private $variantUpdater;

    /**
     * @var VariableUpdater
     */
    private $variableUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;

        $this->variantUpdater = new VariantUpdater();
        $this->variableUpdater = new VariableUpdater();
    }

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->variableUpdater->updateMinPrice($variable);
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->persistenceHelper->isChanged($variable, 'taxGroup')) {
            foreach ($variable->getVariants() as $variant) {
                if ($this->variantUpdater->updateTaxGroup($variant)) {
                    $this->persistenceHelper->persistAndRecompute($variant);
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->variableUpdater->updateStockState($variable);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIABLE;
    }
}
