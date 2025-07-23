<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Export;

use Ekyna\Component\Commerce\Common\Export\AbstractExporter;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Xls;

/**
 * Class SupplierOrderExporter
 * @package Ekyna\Component\Commerce\Supplier\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderExporter extends AbstractExporter
{
    public function __construct(
        protected readonly SupplierOrderRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    /**
     * Export the suppliers expired due orders.
     */
    public function exportSuppliersExpiredDueOrders(): Xls
    {
        return $this->buildFile(
            $this->repository->findSuppliersExpiredDue(),
            'suppliers_expired',
            $this->getDefaultMap()
        );
    }

    /**
     * Export the suppliers fall due orders.
     */
    public function exportSuppliersFallDueOrders(): Xls
    {
        return $this->buildFile(
            $this->repository->findSuppliersFallDue(),
            'suppliers_fall',
            $this->getDefaultMap()
        );
    }

    /**
     * Export the forwarders expired due orders.
     */
    public function exportForwardersExpiredDueOrders(): Xls
    {
        return $this->buildFile(
            $this->repository->findForwardersExpiredDue(),
            'suppliers_expired',
            $this->getDefaultMap()
        );
    }

    /**
     * Export the forwarders fall due orders.
     */
    public function exportForwardersFallDueOrders(): Xls
    {
        return $this->buildFile(
            $this->repository->findForwardersFallDue(),
            'suppliers_fall',
            $this->getDefaultMap()
        );
    }

    /**
     * Returns the default map.
     *
     * @return array
     */
    public function getDefaultMap(): array
    {
        return [
            'number'               => 'number',
            'state'                => 'state',
            'ordered_at'           => function (SupplierOrderInterface $order): ?string {
                if (null !== $date = $order->getOrderedAt()) {
                    return $date->format(DateUtil::DATE_FORMAT);
                }

                return null;
            },
            'completed_at'         => function (SupplierOrderInterface $order): ?string {
                if (null !== $date = $order->getCompletedAt()) {
                    return $date->format(DateUtil::DATE_FORMAT);
                }

                return null;
            },
            'supplier'             => 'supplier.name',
            'payment_total'        => 'paymentTotal',
            'payment_paid_total'   => 'paymentPaidTotal',
            'payment_due_date'     => function (SupplierOrderInterface $order): ?string {
                if (null !== $date = $order->getPaymentDueDate()) {
                    return $date->format(DateUtil::DATE_FORMAT);
                }

                return null;
            },
            'carrier'              => function (SupplierOrderInterface $order): ?string {
                if (null !== $carrier = $order->getCarrier()) {
                    return $carrier->getName();
                }

                return null;
            },
            'forwarder_total'      => 'forwarderTotal',
            'forwarder_paid_total' => 'forwarderPaidTotal',
            'forwarder_due_date'   => function (SupplierOrderInterface $order): ?string {
                if (null !== $date = $order->getForwarderDueDate()) {
                    return $date->format(DateUtil::DATE_FORMAT);
                }

                return null;
            },
        ];
    }
}
