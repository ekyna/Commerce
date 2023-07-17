<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Helper;

use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class SaleItemHelper
 * @package Ekyna\Component\Commerce\Common\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemHelper
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        private readonly SubjectHelperInterface $subjectHelper,
        private readonly ShipmentSubjectCalculatorInterface $shipmentSubjectCalculator,
        private readonly InvoiceSubjectCalculatorInterface $invoiceSubjectCalculator,
    ) {
    }

    /**
     * Initializes the sale item with its subject.
     *
     * @throws IllegalOperationException
     */
    public function initialize(
        SaleItemInterface $item,
        ?SubjectInterface $subject,
        SaleItemEvent     $event = null
    ): SaleItemEvent {
        $this->preventIllegalOperation($item);

        if (null !== $subject) {
            $this->subjectHelper->assign($item, $subject);
        }

        $this->assertAssignedSubject($item);

        if (null === $event) {
            $event = new SaleItemEvent($item);
        } elseif ($item !== $event->getItem()) {
            throw new LogicException('Items do not match');
        }

        $item->setData([]);

        $this->eventDispatcher->dispatch($event, SaleItemEvents::INITIALIZE);

        return $event;
    }

    /**
     * Builds the sale item (with its subject).
     *
     * @throws IllegalOperationException
     */
    public function build(SaleItemInterface $item, SaleItemEvent $event = null): SaleItemEvent
    {
        $this->preventIllegalOperation($item);

        $this->assertAssignedSubject($item);

        if (null === $event) {
            $event = new SaleItemEvent($item);
        } elseif ($item !== $event->getItem()) {
            throw new LogicException('Items do not match');
        }

        $this->eventDispatcher->dispatch($event, SaleItemEvents::BUILD);

        return $event;
    }

    public function isShippedOrInvoiced(SaleItemInterface $item): bool
    {
        if ($this->shipmentSubjectCalculator->isShipped($item)) {
            return true;
        }

        if ($this->invoiceSubjectCalculator->isInvoiced($item)) {
            return true;
        }

        return false;
    }

    public function preventIllegalOperation(SaleItemInterface $item): void
    {
        if ($this->isShippedOrInvoiced($item)) {
            throw new IllegalOperationException('Item is shipped and therefor cannot be changed.');
        }
    }

    protected function assertAssignedSubject(SaleItemInterface $item): void
    {
        if ($item->getSubjectIdentity()->hasIdentity()) {
            return;
        }

        throw new LogicException('No subject assigned.');
    }
}
