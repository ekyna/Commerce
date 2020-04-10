<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DuplicateChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DuplicateChecker extends AbstractChecker
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Assignment duplicates";
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {
        foreach ($this->results as $result) {
            $label = sprintf(
                "Order #%d Subject #%d Assignment #%d : delete",
                $result['order_id'], $result['subject_id'], $result['id']
            );

            $this->actions[] = new Fix(
                $label,
                "DELETE FROM commerce_stock_assignment WHERE id=:id LIMIT 1",
                ['id' => $result['id']]
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function getMap(): array
    {
        return [
            'id'            => 'ID',
            'item_id'       => 'Item',
            'order_id'      => 'Order',
            'subject_id'    => 'Subject',
            'sold_quantity' => 'Sold',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSql(): string
    {
        return <<<SQL
SELECT a2.id, u.product_id as subject_id, o.id as order_id, a2.order_item_id as item_id, a2.sold_quantity
FROM commerce_stock_assignment a2
JOIN commerce_stock_unit u ON u.id=a2.stock_unit_id
JOIN commerce_order_item AS i1 ON i1.id = a2.order_item_id
LEFT JOIN commerce_order_item AS i2 ON i2.id=i1.parent_id
LEFT JOIN commerce_order_item AS i3 ON i3.id=i2.parent_id
LEFT JOIN commerce_order_item AS i4 ON i4.id=i3.parent_id
LEFT JOIN commerce_order_item AS i5 ON i5.id=i4.parent_id
LEFT JOIN commerce_order_item AS i6 ON i6.id=i5.parent_id
JOIN commerce_order AS o ON (
    o.id = i1.order_id OR o.id = i2.order_id OR o.id = i3.order_id 
    OR o.id = i4.order_id OR o.id = i5.order_id OR o.id = i6.order_id
)
WHERE EXISTS(
    SELECT COUNT(a1.id)
    FROM commerce_stock_assignment a1
    WHERE a1.order_item_id = a2.order_item_id
    GROUP BY a1.order_item_id
    HAVING 1 < COUNT(a1.id)
)
AND a2.sold_quantity = 0;
SQL;
    }
}
