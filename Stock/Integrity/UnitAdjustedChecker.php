<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnitAdjustedChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UnitAdjustedChecker extends AbstractChecker
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Stock unit adjusted quantity";
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {
        foreach ($this->results as $unit) {
            $label = sprintf(
                "Unit #%d Subject #%d, adjusted %d -> %d",
                $unit['id'], $unit['product_id'], $unit['adjusted_qty'], $unit['adjusted_sum']
            );

            $this->actions[] = new Fix(
                $label,
                "UPDATE commerce_stock_unit SET adjusted_quantity=:adjusted WHERE id=:id LIMIT 1",
                ['adjusted' => $unit['adjusted_sum'], 'id' => $unit['id']],
                $unit['id']
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function filter(array $result): bool
    {
        return 0 === bccomp($result['adjusted_qty'], $result['adjusted_sum'], 5);
    }

    /**
     * @inheritDoc
     */
    protected function getMap(): array
    {
        return [
            'id'           => 'ID',
            'product_id'   => 'Product',
            'adjusted_qty' => 'Adjusted qty',
            'adjusted_sum' => 'Adjusted sum',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSql(): string
    {
        /** @noinspection SqlAggregates */
        return <<<SQL
SELECT u.id, u.product_id, u.adjusted_quantity AS adjusted_qty, 
IFNULL((
    SELECT SUM(a1.quantity) FROM commerce_stock_adjustment AS a1 
    WHERE a1.stock_unit_id=u.id AND a1.reason IN ('credit', 'found')
), 0) - IFNULL((
    SELECT SUM(a2.quantity) FROM commerce_stock_adjustment AS a2 
    WHERE a2.stock_unit_id=u.id AND a2.reason IN ('debit', 'faulty', 'improper')
), 0) AS adjusted_sum
FROM commerce_stock_unit AS u
HAVING adjusted_qty != adjusted_sum
SQL;
    }
}
