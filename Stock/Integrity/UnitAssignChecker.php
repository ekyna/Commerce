<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnitChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UnitAssignChecker extends AbstractChecker
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Stock unit sold and shipped quantities.";
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {
        foreach ($this->results as $unit) {
            $label = sprintf("Unit #%d Subject #%d", $unit['id'], $unit['product_id']);

            $sets = [];
            $params = [];
            foreach (['sold', 'shipped'] as $field) {
                if (0 === bccomp($pre = $unit[$field . '_qty'], $post = $unit[$field . '_sum'], 5)) {
                    continue;
                }

                $label .= sprintf(", %s: %d -> %d", $field, $pre, $post);
                $sets[] = $field . '_quantity=:' . $field;
                $params[$field] = $post;
            }
            if (empty($sets)) {
                continue;
            }

            $this->actions[] = new Fix(
                $label,
                "UPDATE commerce_stock_unit SET " . implode(',', $sets) . " WHERE id=:id LIMIT 1",
                $params + ['id' => $unit['id']],
                $unit['id']
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function filter(array $result): bool
    {
        return 0 === bccomp($result['sold_sum'], $result['sold_qty'], 5)
            && 0 === bccomp($result['shipped_sum'], $result['shipped_qty'], 5);
    }

    /**
     * @inheritDoc
     */
    protected function getMap(): array
    {
        return [
            'id'          => 'ID',
            'product_id'  => 'Product',
            'sold_qty'    => 'Sold qty',
            'sold_sum'    => 'Sold sum',
            'shipped_qty' => 'Shipped qty',
            'shipped_sum' => 'Shipped sum',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSql(): string
    {
        /** @noinspection SqlAggregates */
        return <<<SQL
SELECT u.id, 
       u.product_id, 
       u.sold_quantity AS sold_qty, 
       u.shipped_quantity AS shipped_qty, 
       SUM(a.sold_quantity) AS sold_sum,
       SUM(a.shipped_quantity) AS shipped_sum
FROM commerce_stock_unit AS u
JOIN commerce_stock_assignment AS a ON a.stock_unit_id=u.id
GROUP BY u.id
HAVING sold_qty != sold_sum OR shipped_qty != shipped_sum
SQL;
    }
}
