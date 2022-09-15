<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\LockCheckerAwareTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function array_diff;
use function array_keys;
use function sprintf;

/**
 * Class AbstractInvoiceListener
 * @package Ekyna\Component\Commerce\Invoice\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceListener
{
    use LockCheckerAwareTrait;

    protected readonly PersistenceHelperInterface      $persistenceHelper;
    protected readonly GeneratorInterface              $invoiceNumberGenerator;
    protected readonly GeneratorInterface              $creditNumberGenerator;
    protected readonly DocumentBuilderInterface        $invoiceBuilder;
    protected readonly DocumentCalculatorInterface     $invoiceCalculator;
    protected readonly InvoicePaymentResolverInterface $invoicePaymentResolver;
    protected readonly AuthorizationCheckerInterface $authorizationChecker;

    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    public function setInvoiceNumberGenerator(GeneratorInterface $generator): void
    {
        $this->invoiceNumberGenerator = $generator;
    }

    public function setCreditNumberGenerator(GeneratorInterface $generator): void
    {
        $this->creditNumberGenerator = $generator;
    }

    public function setInvoiceBuilder(DocumentBuilderInterface $builder): void
    {
        $this->invoiceBuilder = $builder;
    }

    public function setInvoiceCalculator(DocumentCalculatorInterface $calculator): void
    {
        $this->invoiceCalculator = $calculator;
    }

    public function setInvoicePaymentResolver(InvoicePaymentResolverInterface $invoicePaymentResolver): void
    {
        $this->invoicePaymentResolver = $invoicePaymentResolver;
    }

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        if ($invoice->getSale()->isSample()) {
            throw new Exception\LogicException("Can't create invoice for sample sale");
        }

        // Generate number and key
        $changed = $this->generateNumber($invoice);

        // Updates the invoice data
        $changed = $this->invoiceBuilder->update($invoice) || $changed;

        // Updates the totals
        $changed = $this->updateTotals($invoice) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($invoice, false);
        }

        $sale = $this->getSaleFromInvoice($invoice);
        if ($sale instanceof InvoiceSubjectInterface) {
            $sale->addInvoice($invoice); // TODO wtf ?
        }

        $this->scheduleSaleContentChangeEvent($sale);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        $this->preventForbiddenChange($invoice);

        $this->scheduleSaleContentChangeEvent($this->getSaleFromInvoice($invoice));
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        if ($this->lockChecker->isLocked($invoice)) {
            throw new Exception\IllegalOperationException(sprintf(
                'Invoice %s is locked.',
                $invoice->getNumber()
            ));
        }

        $sale = $this->getSaleFromInvoice($invoice);

        $sale->removeInvoice($invoice);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    public function onContentChange(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        if (!$this->persistenceHelper->isScheduledForRemove($invoice)) {
            if ($this->updateTotals($invoice)) {
                $this->persistenceHelper->persistAndRecompute($invoice, false);
            }
        }

        $sale = $this->getSaleFromInvoice($invoice);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    public function onPreUpdate(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        // Pre load sale's invoices collection
        /** @var InvoiceSubjectInterface $sale */
        $sale = $invoice->getSale();
        $sale->getInvoices()->toArray();
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        // Pre load sale's invoices collection
        /** @var InvoiceSubjectInterface $sale */
        $sale = $invoice->getSale();
        $sale->getInvoices()->toArray();
    }

    protected function updateTotals(InvoiceInterface $invoice): bool
    {
        $changed = $this->invoiceCalculator->calculate($invoice);

        if ($changed) {
            $this->invoicePaymentResolver->clearSale($invoice->getSale());

            foreach ($invoice->getLines() as $line) {
                $this->persistenceHelper->persistAndRecompute($line, false);
            }
        }

        return $changed;
    }

    /**
     * @return bool Whether the invoice number has been generated or not.
     */
    protected function generateNumber(InvoiceInterface $invoice): bool
    {
        if (!empty($invoice->getNumber())) {
            return false;
        }

        if ($invoice->isCredit()) {
            $invoice->setNumber($this->creditNumberGenerator->generate($invoice));
        } else {
            $invoice->setNumber($this->invoiceNumberGenerator->generate($invoice));
        }

        return true;
    }

    /**
     * Prevents some of the invoice's properties to change.
     */
    protected function preventForbiddenChange(InvoiceInterface $invoice): void
    {
        if (empty($cs = $this->persistenceHelper->getChangeSet($invoice))) {
            return;
        }

        if (isset($cs['type']) && ($cs['type'][0] !== $cs['type'][1])) {
            throw new Exception\IllegalOperationException(
                'Changing the invoice type is not yet supported.'
            );
        }

        // Only comment, description, paid total and real paid total can change for locked invoices
        $allowed = ['comment', 'description', 'paidTotal', 'realPaidTotal'];
        if (empty(array_diff(array_keys($cs), $allowed))) {
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return;
        }

        if (!$this->lockChecker->isLocked($invoice)) {
            return;
        }

        throw new Exception\IllegalOperationException(sprintf(
            'Invoice %s is locked.',
            $invoice->getNumber()
        ));
    }

    /**
     * Returns the invoice's sale.
     *
     * @return SaleInterface|InvoiceSubjectInterface
     */
    protected function getSaleFromInvoice(InvoiceInterface $invoice): SaleInterface
    {
        if (null === $sale = $invoice->getSale()) {
            $cs = $this->persistenceHelper->getChangeSet($invoice, $this->getSalePropertyPath());
            if (!empty($cs)) {
                $sale = $cs[0];
            }
        }

        if (!$sale instanceof SaleInterface) {
            throw new Exception\RuntimeException('Failed to retrieve invoice\'s sale.');
        }

        return $sale;
    }

    /**
     * Dispatches the sale content change event.
     */
    abstract protected function scheduleSaleContentChangeEvent(SaleInterface $sale): void;

    /**
     * Returns the invoice from the event.
     *
     * @throws Exception\UnexpectedTypeException
     */
    abstract protected function getInvoiceFromEvent(ResourceEventInterface $event): InvoiceInterface;

    /**
     * Returns the invoice's sale property path.
     */
    abstract protected function getSalePropertyPath(): string;
}
