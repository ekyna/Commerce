<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\LockCheckerAwareTrait;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AbstractInvoiceItemListener
 * @package Ekyna\Component\Commerce\Invoice\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceItemListener
{
    use LockCheckerAwareTrait;

    protected readonly PersistenceHelperInterface    $persistenceHelper;
    protected readonly ContextProviderInterface      $contextProvider;
    protected readonly TaxResolverInterface          $taxResolver;
    protected readonly AuthorizationCheckerInterface $authorizationChecker;

    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    public function setContextProvider(ContextProviderInterface $contextProvider): void
    {
        $this->contextProvider = $contextProvider;
    }

    public function setTaxResolver(TaxResolverInterface $taxResolver): void
    {
        $this->taxResolver = $taxResolver;
    }

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $item = $this->getInvoiceItemFromEvent($event);

        $this->calculate($item);

        $this->persistenceHelper->persistAndRecompute($item, false);

        $this->scheduleInvoiceContentChangeEvent($item->getInvoice());
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $item = $this->getInvoiceItemFromEvent($event);

        $this->preventForbiddenChange($item);

        if (!$this->persistenceHelper->isChanged($item, ['unit', 'quantity', 'taxGroup'])) {
            return;
        }

        $this->calculate($item);

        $this->persistenceHelper->persistAndRecompute($item, false);

        $this->scheduleInvoiceContentChangeEvent($item->getInvoice());
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $item = $this->getInvoiceItemFromEvent($event);

        // Get invoice from change set if null
        if (null === $invoice = $item->getInvoice()) {
            $invoice = $this->persistenceHelper->getChangeSet($item, 'invoice')[0];
        }

        if ($this->lockChecker->isLocked($invoice) && !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            throw new Exception\IllegalOperationException(
                'This invoice is locked.'
            );
        }

        $this->scheduleInvoiceContentChangeEvent($invoice);
    }

    /**
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
        $discount = new Decimal(0);
        $discountRates = [];

        // Base
        $base = $ati
            ? round($gross - $discount, 5)
            : Money::round($gross - $discount, $currency);

        // Taxes
        $context = $this->contextProvider->getContext($sale);
        $taxes = $this->taxResolver->resolveTaxes($item, $context);

        $tax = new Decimal(0);
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
     * Prevents some of the invoice item's fields to change.
     */
    protected function preventForbiddenChange(Model\InvoiceItemInterface $item): void
    {
        if (empty($this->persistenceHelper->getChangeSet($item))) {
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return;
        }

        if (!$this->lockChecker->isLocked($item->getInvoice())) {
            return;
        }

        throw new Exception\IllegalOperationException(
            'This invoice is locked.'
        );
    }

    /**
     * Schedules the invoice content change event.
     */
    abstract protected function scheduleInvoiceContentChangeEvent(Model\InvoiceInterface $invoice): void;

    /**
     * Returns the invoice item from the event.
     */
    abstract protected function getInvoiceItemFromEvent(ResourceEventInterface $event): Model\InvoiceItemInterface;
}
