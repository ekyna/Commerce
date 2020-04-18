<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FinalChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FinalChecker extends AbstractChecker
{
    /** @var array */
    private $map;

    /** @var bool */
    private $error;


    /**
     * @inheritDoc
     */
    public function check(OutputInterface $output): bool
    {
        $this->doCheck(
            $output,
            'Assignments: shipped <= sold',
            <<<SQL
SELECT a.id, u.product_id, a.sold_quantity, a.shipped_quantity
FROM commerce_stock_assignment a
JOIN commerce_stock_unit u ON u.id=a.stock_unit_id 
WHERE a.shipped_quantity > a.sold_quantity
SQL,
            [
                'id'               => 'ID',
                'product_id'       => 'Subject',
                'sold_quantity'    => 'Sold',
                'shipped_quantity' => 'Shipped',
            ]
        );

        $this->doCheck(
            $output,
            'Units: shipped <= sold',
            <<<SQL
SELECT u.id, u.product_id, u.sold_quantity, u.shipped_quantity
FROM commerce_stock_unit u
WHERE u.shipped_quantity > u.sold_quantity
SQL,
            [
                'id'               => 'ID',
                'product_id'       => 'Subject',
                'sold_quantity'    => 'Sold',
                'shipped_quantity' => 'Shipped',
            ]
        );

        $this->doCheck(
            $output,
            'Units: received <= ordered',
            <<<SQL
SELECT u.id, u.product_id, u.ordered_quantity, u.received_quantity
FROM commerce_stock_unit u
WHERE u.received_quantity > u.ordered_quantity
SQL,
            [
                'id'                => 'ID',
                'product_id'        => 'Subject',
                'ordered_quantity'  => 'Ordered',
                'received_quantity' => 'Received',
            ]
        );

        $this->doCheck(
            $output,
            'Supplied units: sold <= ordered + adjusted',
            <<<SQL
SELECT u.id, u.product_id, u.ordered_quantity, u.adjusted_quantity, u.sold_quantity
FROM commerce_stock_unit u
WHERE u.sold_quantity > (u.ordered_quantity + u.adjusted_quantity)
  AND u.supplier_order_item_id IS NOT NULL
SQL,
            [
                'id'                => 'ID',
                'product_id'        => 'Subject',
                'ordered_quantity'  => 'Ordered',
                'adjusted_quantity' => 'Adjusted',
                'sold_quantity'     => 'Sold',
            ]
        );

        $this->doCheck(
            $output,
            'Supplied units: shipped <= received + adjusted',
            <<<SQL
SELECT u.id, u.product_id, u.received_quantity, u.adjusted_quantity, u.shipped_quantity
FROM commerce_stock_unit u
WHERE u.shipped_quantity > (u.received_quantity + u.adjusted_quantity)
  AND u.supplier_order_item_id IS NOT NULL
SQL,
            [
                'id'                => 'ID',
                'product_id'        => 'Subject',
                'received_quantity' => 'Received',
                'adjusted_quantity' => 'Adjusted',
                'shipped_quantity'  => 'Shipped',
            ]
        );

        $this->doCheck(
            $output,
            'Not supplied units: sold <= adjusted',
            <<<SQL
SELECT u.id, u.product_id, u.adjusted_quantity, u.sold_quantity
FROM commerce_stock_unit u
JOIN product_product p ON p.id=u.product_id 
WHERE u.sold_quantity > u.adjusted_quantity
  AND p.stock_mode != 'manual'
  AND u.supplier_order_item_id IS NULL AND state != 'new'
  AND u.adjusted_quantity > 0 AND u.sold_quantity > u.adjusted_quantity
SQL,
            [
                'id'                => 'ID',
                'product_id'        => 'Subject',
                'adjusted_quantity' => 'Adjusted',
                'sold_quantity'     => 'Sold',
            ]
        );

        $this->doCheck(
            $output,
            'Not supplied units: shipped <= adjusted',
            <<<SQL
SELECT u.id, u.product_id, u.state, u.adjusted_quantity, u.shipped_quantity
FROM commerce_stock_unit u
JOIN product_product p ON p.id=u.product_id 
WHERE u.shipped_quantity > u.adjusted_quantity
  AND p.stock_mode != 'manual'
  AND u.supplier_order_item_id IS NULL
SQL,
            [
                'id'                => 'ID',
                'product_id'        => 'Subject',
                'adjusted_quantity' => 'Adjusted',
                'shipped_quantity'  => 'Shipped',
            ]
        );

        if ($this->error) {
            throw new \Exception('Final integrity check failed.');
        }

        return false;
    }

    private function doCheck(OutputInterface $output, string $title, string $sql, array $map): void
    {
        $output->write($title . ' ... ');

        $this->results = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($this->results)) {
            $output->writeln('<info>ok</info>');

            return;
        }

        $output->writeln('<error>error</error>');

        $this->map = $map;
        $this->error = true;

        $this->display($output);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Final checks\n";
    }

    /**
     * Returns the map.
     *
     * @return array
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
