<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Export;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Export\AbstractExporter;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;

/**
 * Class SupplierOrderItemExporter
 * @package Ekyna\Component\Commerce\Supplier\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemExporter extends AbstractExporter
{
    private SupplierOrderItemRepositoryInterface $itemRepository;
    private CurrencyConverterInterface           $currencyConverter;

    public function __construct(
        SupplierOrderItemRepositoryInterface $itemRepository,
        CurrencyConverterInterface           $currencyConverter
    ) {
        parent::__construct();

        $this->itemRepository = $itemRepository;
        $this->currencyConverter = $currencyConverter;
    }

    public function exportPaidButNotDelivered(): string
    {
        return $this->buildFile(
            $this->itemRepository->findPaidAndNotDelivered(),
            'supplier_order_item_paid_but_not_delivered',
            $this->getDefaultMap()
        );
    }

    protected function getDefaultMap(): array
    {
        return [
            'id'           => 'product.subjectIdentity.identifier',
            'number'       => 'order.number',
            'supplier'     => 'order.supplier.name',
            'ordered_at'   => function (SupplierOrderItemInterface $item): string {
                return $item->getOrder()->getOrderedAt()->format('Y-m-d');
            },
            'paid_at'      => function (SupplierOrderItemInterface $item): string {
                return $item->getOrder()->getPaymentDate()->format('Y-m-d');
            },
            'reference'    => null,
            'designation'  => null,
            'quantity'     => null,
            'net_price'    => null,
            'currency'     => 'order.currency.code',
            'valorization' => function (SupplierOrderItemInterface $item): string {
                return $this
                    ->currencyConverter
                    ->convertWithSubject(
                        $item->getNetPrice(),
                        $item->getOrder(),
                        $this->currencyConverter->getDefaultCurrency(),
                        false
                    )
                    ->mul($item->getQuantity())
                    ->toFixed(2);
            },
        ];
    }
}
