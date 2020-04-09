<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnitReceivedChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UnitReceivedChecker extends AbstractChecker
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Stock unit received quantity";
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {
        foreach ($this->results as $unit) {
            $label = sprintf(
                "Unit #%d Subject #%d, received %d -> %d",
                $unit['id'], $unit['product_id'], $unit['received_qty'], $unit['received_sum']
            );

            $this->actions[] = new Fix(
                $label,
                "UPDATE commerce_stock_unit SET received_quantity=:received WHERE id=:id LIMIT 1",
                ['received' => $unit['received_sum'], 'id' => $unit['id']],
                $unit['id']
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function filter(array $result): bool
    {
        return 0 === bccomp($result['received_qty'], $result['received_sum'], 5);
    }

    /**
     * @inheritDoc
     */
    protected function getMap(): array
    {
        return [
            'id'           => 'ID',
            'product_id'   => 'Product',
            'received_qty' => 'Received qty',
            'received_sum' => 'Received sum',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSql(): string
    {
        /** @noinspection SqlAggregates */
        return <<<SQL
SELECT u.id, u.product_id, u.received_quantity AS received_qty, SUM(di.quantity) AS received_sum
FROM commerce_stock_unit AS u
JOIN commerce_supplier_order_item AS oi ON oi.id=u.supplier_order_item_id
JOIN commerce_supplier_order AS o ON o.id=oi.supplier_order_id
LEFT JOIN commerce_supplier_delivery_item AS di ON di.supplier_order_item_id=oi.id
GROUP BY u.id
HAVING received_qty != received_sum
SQL;
    }
}
