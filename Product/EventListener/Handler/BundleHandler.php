<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Commerce\Product\Updater\BundleUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class BundleHandler
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleHandler extends AbstractHandler
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockUpdater;

    /**
     * @var BundleUpdater
     */
    private $bundleUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param StockSubjectUpdaterInterface     $stockUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockSubjectUpdaterInterface $stockUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUpdater = $stockUpdater;

        $this->bundleUpdater = new BundleUpdater();
    }

    /**
     * @inheritDoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        return $this->stockUpdater->update($bundle);
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        if ($this->persistenceHelper->isChanged($bundle, ['inStock', 'orderedStock', 'estimatedDateOfArrival'])) {
            return $this->stockUpdater->updateStockState($bundle);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function handleDelete(ResourceEventInterface $event)
    {

    }

    /**
     * @inheritDoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        return $this->bundleUpdater->updateStock($bundle);
    }

    /**
     * @inheritDoc
     */
    public function handleStockUnitChange(ResourceEventInterface $event)
    {

    }

    /**
     * @inheritDoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_BUNDLE;
    }
}
