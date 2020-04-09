<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnitOrderedChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UnitOrderedChecker extends AbstractChecker
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Stock unit ordered quantity";
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {
        foreach ($this->results as $unit) {
            $label = sprintf(
                "Unit #%d Subject #%d, ordered %d -> %d",
                $unit['id'], $unit['product_id'], $unit['ordered_qty'], $unit['ordered_sum']
            );

            $this->actions[] = new Fix(
                $label,
                "UPDATE commerce_stock_unit SET ordered_quantity=:ordered WHERE id=:id LIMIT 1",
                ['ordered' => $unit['ordered_sum'], 'id' => $unit['id']],
                $unit['id']
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function filter(array $result): bool
    {
        return 0 === bccomp($result['ordered_qty'], $result['ordered_sum'], 5);
    }

    /**
     * @inheritDoc
     */
    protected function getMap(): array
    {
        return [
            'id'          => 'ID',
            'product_id'  => 'Product',
            'ordered_qty' => 'Ordered qty',
            'ordered_sum' => 'Ordered sum',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSql(): string
    {
        /** @noinspection SqlAggregates */
        return <<<SQL
SELECT u.id, u.product_id, u.ordered_quantity AS ordered_qty, SUM(oi.quantity) AS ordered_sum
FROM commerce_stock_unit AS u
JOIN commerce_supplier_order_item AS oi ON oi.id=u.supplier_order_item_id
JOIN commerce_supplier_order AS o ON o.id=oi.supplier_order_id
WHERE o.state IN ('validated', 'partial', 'received', 'completed')
GROUP BY u.id
HAVING ordered_qty != ordered_sum
SQL;

    }
}
