<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\MessageHandler;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceMarginCalculatorFactory;
use Ekyna\Component\Commerce\Order\Message\UpdateOrderMargin;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Class UpdateOrderMarginHandler
 * @package Ekyna\Component\Commerce\Order\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateOrderMarginHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface       $repository,
        private readonly OrderUpdaterInterface          $updater,
        private readonly InvoiceMarginCalculatorFactory $invoiceMarginCalculatorFactory, // TODO InvoiceUpdaterInterface
        private readonly ResourceManagerInterface       $manager,
    ) {
    }

    public function __invoke(UpdateOrderMargin $message): void
    {
        if (null === $order = $this->repository->find($message->orderId)) {
            return;
        }

        $changed = false;

        if ($this->updater->updateMargin($order)) {
            $this->manager->persist($order);
            $changed = true;
        }

        $calculator = $this->invoiceMarginCalculatorFactory->create();

        foreach ($order->getInvoices() as $invoice) {
            $margin = $calculator->calculateInvoice($invoice);

            if ($invoice->getMargin()->equals($margin)) {
                continue;
            }

            $invoice->setMargin($margin);

            $this->manager->persist($invoice);
            $changed = true;
        }

        if (!$changed) {
            return;
        }

        $this->manager->flush();
    }
}
