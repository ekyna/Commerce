<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Updater;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceMarginCalculatorFactory;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;

/**
 * Class OrderInvoiceUpdater
 * @package Ekyna\Component\Commerce\Order\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceUpdater implements OrderInvoiceUpdaterInterface
{
    private InvoiceMarginCalculatorFactory $marginCalculatorFactory;

    /**
     * @param InvoiceMarginCalculatorFactory $marginCalculatorFactory
     */
    public function __construct(InvoiceMarginCalculatorFactory $marginCalculatorFactory)
    {
        $this->marginCalculatorFactory = $marginCalculatorFactory;
    }

    /**
     * @inheritDoc
     */
    public function updateMargin(OrderInvoiceInterface $invoice): bool
    {
        $margin = $this->marginCalculatorFactory->create()->calculateInvoice($invoice);

        if ($invoice->getMargin()->equals($margin)) {
            return false;
        }

        $invoice->setMargin($margin);

        return true;
    }
}
