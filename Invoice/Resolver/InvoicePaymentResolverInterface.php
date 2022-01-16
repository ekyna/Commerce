<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Interface InvoicePaymentResolverInterface
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoicePaymentResolverInterface
{
    /**
     * Clears cached results.
     */
    public function clear(): void;

    /**
     * Clears the sale's invoices cached results.
     */
    public function clearSale(SaleInterface $sale): void;

    /**
     * Resolves the invoice's payments.
     *
     * @return array<Model\InvoicePayment>
     */
    public function resolve(Model\InvoiceInterface $invoice, bool $invoices = true): array;

    /**
     * Returns the invoice's paid total.
     */
    public function getPaidTotal(Model\InvoiceInterface $invoice): Decimal;

    /**
     * Returns the invoice's real paid total (default currency).
     */
    public function getRealPaidTotal(Model\InvoiceInterface $invoice): Decimal;
}
