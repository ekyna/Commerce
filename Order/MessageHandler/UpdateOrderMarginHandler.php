<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\MessageHandler;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceMarginCalculatorFactory;
use Ekyna\Component\Commerce\Order\Message\UpdateOrderMargin;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;

use function get_class;

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
        private readonly ManagerFactoryInterface        $managerFactory,
    ) {
    }

    public function __invoke(UpdateOrderMargin $message): void
    {
        if (null === $order = $this->repository->find($message->orderId)) {
            return;
        }

        $changed = false;
        $manager = $this->managerFactory->getManager(get_class($order));
        if ($this->updater->updateMargin($order)) {
            $manager->persist($order);
            $changed = true;
        }

        $calculator = $this->invoiceMarginCalculatorFactory->create();

        foreach ($order->getInvoices() as $invoice) {
            $margin = $calculator->calculateInvoice($invoice);

            if ($invoice->getMargin()->equals($margin)) {
                continue;
            }

            $invoice->setMargin($margin);

            $this->managerFactory->getManager(get_class($invoice))->persist($invoice);
            $changed = true;
        }

        if (!$changed) {
            return;
        }

        $manager->flush();
    }
}
