<?php

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitLinker
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitLinker implements StockUnitLinkerInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $stockUnitUpdater;

    /**
     * @var StockUnitResolverInterface
     */
    protected $unitResolver;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockUnitUpdaterInterface  $stockUnitUpdater
     * @param StockUnitResolverInterface $unitResolver
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitUpdaterInterface $stockUnitUpdater,
        StockUnitResolverInterface $unitResolver,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUnitUpdater = $stockUnitUpdater;
        $this->unitResolver = $unitResolver;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritdoc
     */
    public function linkItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        if (!$supplierOrderItem->hasSubjectIdentity()) {
            return;
        }

        // Find 'unlinked' stock units ordered (+ Cached 'new' stock units look up)
        if (null !== $stockUnit = $this->unitResolver->findLinkable($supplierOrderItem)) {
            $stockUnit->setSupplierOrderItem($supplierOrderItem);
        } else {
            $stockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);
        }

        $stockUnit
            ->setSupplierOrderItem($supplierOrderItem)
            ->setEstimatedDateOfArrival($supplierOrderItem->getOrder()->getEstimatedDateOfArrival());

        $this->updatePrice($stockUnit);

        $this->stockUnitUpdater->updateOrdered($stockUnit, $supplierOrderItem->getQuantity(), false);
    }

    /**
     * @inheritdoc
     */
    public function applyItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        if (!$supplierOrderItem->hasSubjectIdentity()) {
            return false;
        }

        // Supplier order item has been previously linked to a stock unit.
        $stockUnit = $supplierOrderItem->getStockUnit();

        $changed = false;

        // Update net price if needed
        if ($this->persistenceHelper->isChanged($supplierOrderItem, 'netPrice')) {
            if ($this->updatePrice($stockUnit)) {
                $this->persistenceHelper->persistAndRecompute($stockUnit, false);
                $changed = true;
            }
        }

        // Update ordered quantity if needed
        if ($this->persistenceHelper->isChanged($supplierOrderItem, 'quantity')) {
            $cs = $this->persistenceHelper->getChangeSet($supplierOrderItem, 'quantity');
            if (0 != $cs[1] - $cs[0]) { // TODO Use packaging format
                $this->stockUnitUpdater->updateOrdered($stockUnit, $supplierOrderItem->getQuantity(), false);
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function unlinkItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        if (!$supplierOrderItem->hasSubjectIdentity()) {
            return;
        }

        if (null === $stockUnit = $supplierOrderItem->getStockUnit()) {
            return;
        }

        // Unlink stock unit by setting supplier order item to null and ordered quantity to zero
        $stockUnit
            ->setSupplierOrderItem(null)
            ->setNetPrice(null)
            ->setEstimatedDateOfArrival(null);

        $this->stockUnitUpdater->updateOrdered($stockUnit, 0, false);
    }

    /**
     * Updates the stock unit price.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return bool Whether or not the net price has been updated.
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    private function updatePrice(StockUnitInterface $stockUnit)
    {
        $price = null;

        if (null !== $item = $stockUnit->getSupplierOrderItem()) {
            if (null === $order = $item->getOrder()) {
                throw new StockLogicException("Supplier order item's order must be set at this point.");
            }

            $currency = $order->getCurrency()->getCode();
            $date = $order->getPaymentDate();
            if ($date > new \DateTime()) {
                $date = null;
            }

            $price = $this->currencyConverter->convert($item->getNetPrice(), $currency, null, $date);
        }

        if (0 !== Money::compare($stockUnit->getNetPrice(), $price, $this->currencyConverter->getDefaultCurrency())) {
            $stockUnit->setNetPrice($price);

            return true;
        }

        return false;
    }
}
