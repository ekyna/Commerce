<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Commerce\Product\Updater\VariableUpdater;
use Ekyna\Component\Commerce\Product\Updater\VariantUpdater;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class VariantHandler
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantHandler extends AbstractHandler
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
     * @inheritDoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        $changed = false;

        // Generate variant designation if needed
        if (0 === strlen($variant->getDesignation()) && $this->variantUpdater->updateDesignation($variant)) {
            $changed = true;
        }

        // Set tax group regarding to parent/variable if needed
        if ($this->variantUpdater->updateTaxGroup($variant)) {
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        if (null === $variable = $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }

        $changed = false;

        // Generate variant designation if needed
        if (0 === strlen($variant->getDesignation()) && $this->variantUpdater->updateDesignation($variant)) {
            $changed = true;
        }

        // Update parent/variable minimum price if variant price has changed
        if ($this->persistenceHelper->isChanged($variant, 'netPrice')) {
            if ($this->variableUpdater->updateMinPrice($variable)) {
                $this->persistenceHelper->persistAndRecompute($variable);
            }
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIANT;
    }
}
