<?php

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractInvoiceItemListener
 * @package Ekyna\Component\Commerce\Invoice\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceItemListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var ContextProviderInterface
     */
    protected $contextProvider;

    /**
     * @var TaxResolverInterface
     */
    protected $taxResolver;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the context provider.
     *
     * @param ContextProviderInterface $contextProvider
     */
    public function setContextProvider(ContextProviderInterface $contextProvider): void
    {
        $this->contextProvider = $contextProvider;
    }

    /**
     * Sets the tax resolver.
     *
     * @param TaxResolverInterface $taxResolver
     */
    public function setTaxResolver(TaxResolverInterface $taxResolver): void
    {
        $this->taxResolver = $taxResolver;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $item = $this->getInvoiceItemFromEvent($event);

        $this->calculate($item);

        $this->persistenceHelper->persistAndRecompute($item, false);

        $this->scheduleInvoiceContentChangeEvent($item->getInvoice());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $item = $this->getInvoiceItemFromEvent($event);

        if (!$this->persistenceHelper->isChanged($item, ['unit', 'quantity', 'taxGroup'])) {
            return;
        }

        $this->calculate($item);

        $this->persistenceHelper->persistAndRecompute($item, false);

        $this->scheduleInvoiceContentChangeEvent($item->getInvoice());
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $item = $this->getInvoiceItemFromEvent($event);

        // Get invoice from change set if null
        if (null === $invoice = $item->getInvoice()) {
            $invoice = $this->persistenceHelper->getChangeSet($item, 'invoice')[0];
        }

        $this->scheduleInvoiceContentChangeEvent($invoice);
    }

    /**
     * @param Model\InvoiceItemInterface $item
     *
     * @see \Ekyna\Component\Commerce\Common\Calculator\AmountCalculator::calculateSaleItem()
     */
    protected function calculate(Model\InvoiceItemInterface $item): void
    {
        $document = $item->getDocument();
        $sale = $document->getSale();
        $ati = $sale->isAtiDisplayMode();

        $currency = $document->getCurrency();

        // Unit, gross
        $unit = $item->getUnit();
        $gross = $unit * $item->getQuantity();

        // TODO (or not) Discounts
        $discount = 0;
        $discountRates = [];

        // Base
        $base = $ati
            ? round($gross - $discount, 5)
            : Money::round($gross - $discount, $currency);

        // Taxes
        $context = $this->contextProvider->getContext($sale);
        $taxes = $this->taxResolver->resolveTaxes($item, $context);

        $tax = 0;
        $taxRates = [];
        foreach ($taxes as $model) {
            $rate = $model->getRate();
            $taxRates[$model->getName()] = $rate;
            // Calculate taxation as ATI - NET
            $tax += Money::round($base * (1 + $rate / 100), $currency) - Money::round($base, $currency);
        }

        // Total
        $total = Money::round($base + $tax, $currency);

        // ATI display case
        if ($ati) {
            $gross = Money::round($gross, $currency);
            $discount = Money::round($discount, $currency);
            $base = Money::round($base, $currency);
            $total = Money::round($total, $currency);
            $tax = Money::round($total - $base, $currency);
        }

        $item
            ->setGross($gross)
            ->setDiscount($discount)
            ->setDiscountRates($discountRates)
            ->setBase($base)
            ->setTax($tax)
            ->setTaxRates($taxRates)
            ->setTotal($total);
    }

    /**
     * Schedules the invoice content change event.
     *
     * @param Model\InvoiceInterface $invoice
     */
    abstract protected function scheduleInvoiceContentChangeEvent(Model\InvoiceInterface $invoice): void;

    /**
     * Returns the invoice item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\InvoiceItemInterface
     * @throws Exception\InvalidArgumentException
     */
    abstract protected function getInvoiceItemFromEvent(ResourceEventInterface $event): Model\InvoiceItemInterface;
}
