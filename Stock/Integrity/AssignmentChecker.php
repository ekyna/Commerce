<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AssignmentChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AssignmentChecker extends AbstractChecker
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Assignment sold and shipped quantities";
    }

    /**
     * @inheritDoc
     */
    protected function normalize(array &$result): void
    {
        // Calculate shipped sum from shipments and returns
        $result['shipped_sum'] = $result['shipment_sum'] - $result['return_sum'];
        if (0 > $result['shipped_sum']) {
            throw new \RuntimeException("Negative shipped quantity for order #{$result['order_id']}");
        }

        // Calculate sold sum
        if ($result['is_sample']) {
            // Sample orders does not have invoices
            $result['sold_sum'] = $result['item_sum'];

            if ($result['is_released']) {
                // Released orders : use shipped sum if lower
                $result['sold_sum'] = min($result['sold_sum'], $result['shipped_sum']);
            }
        } elseif ($result['is_released']) {
            throw new \RuntimeException("Released non sample order #{$result['order_id']}");
        } else {
            // Regular case
            $result['sold_sum'] = max($result['item_sum'], $result['invoice_sum']) - $result['credit_sum'];
        }

        // Shipped sum must be greater than or equal to zero
        if (1 === bccomp(0, $result['shipped_sum'], 5)) {
            throw new \RuntimeException("Negative shipped quantity for order #{$result['order_id']}");
        }

        // Sold sum must be greater than or equal to zero
        if (1 === bccomp(0, $result['sold_sum'], 5)) {
            throw new \RuntimeException("Negative sold quantity for order #{$result['order_id']}");
        }

        // Shipped sum must be lower than or equal to sold sum
        if (1 === bccomp($result['shipped_sum'], $result['sold_sum'], 5)) {
            throw new \RuntimeException("Shipped quantity greater than sold quantity for order #{$result['order_id']}");
        }
    }

    /**
     * @inheritDoc
     */
    protected function filter(array $result): bool
    {
        // Skip if sold and sum quantities are equal
        return 0 === bccomp($result['sold_sum'], $result['sold_qty'], 5)
            && 0 === bccomp($result['shipped_sum'], $result['shipped_qty'], 5);
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {
        $assignments = [];
        $units = [];

        $selectAssignments = $this->connection->prepare(<<<SQL
SELECT a.id as a_id,
        a.sold_quantity as a_sold, 
        a.shipped_quantity as a_shipped, 
        u.id as u_id, 
        u.sold_quantity as u_sold, 
        u.shipped_quantity as u_shipped,
        u.ordered_quantity as u_ordered, 
        u.adjusted_quantity as u_adjusted,
        u.received_quantity as u_received
FROM commerce_stock_assignment a 
JOIN commerce_stock_unit u ON u.id=a.stock_unit_id 
WHERE a.order_item_id=:item_id
SQL
        );

        foreach ($this->results as $item) {
            $soldDelta = $item['sold_sum'] - $item['sold_qty'];
            $shippedDelta = $item['shipped_sum'] - $item['shipped_qty'];
            if (0 === bccomp(0, $soldDelta, 5) && 0 === bccomp(0, $shippedDelta, 5)) {
                continue;
            }

            $orderId = $item['order_id'];
            $itemId = $item['item_id'];
            $subjectId = $item['subject_id'];

            $selectAssignments->execute(['item_id' => $itemId]);

            while (false !== $data = $selectAssignments->fetch(\PDO::FETCH_ASSOC)) {
                // Cache assignment change set
                $aId = $data['a_id'];
                if (!isset($assignments[$aId])) {
                    $assignments[$aId] = [
                        'order'   => $orderId,
                        'item'    => $itemId,
                        'subject' => $subjectId,
                        'sold'    => [$data['a_sold'], $data['a_sold']],
                        'shipped' => [$data['a_shipped'], $data['a_shipped']],
                    ];
                }
                $assignment = &$assignments[$aId];

                // Cache unit change set
                $uId = $data['u_id'];
                if (!isset($units[$uId])) {
                    $units[$uId] = [
                        'order'   => $orderId,
                        'item'    => $itemId,
                        'subject' => $subjectId,
                        'sold'    => [$data['u_sold'], $data['u_sold']],
                        'shipped' => [$data['u_shipped'], $data['u_shipped']],
                    ];
                }
                $unit = &$units[$uId];

                // Shipped change
                if (0 < $shippedDelta) {
                    // Credit case
                    $shippedQty = max(0, min(
                        $shippedDelta,
                        // Lower than received + adjusted
                        $data['u_received'] + $data['u_adjusted'] - $unit['shipped'][1]
                    ));
                } else {
                    // Debit case
                    $shippedQty = min(0, max(
                        $shippedDelta,
                        // Greater than zero
                        -$assignment['shipped'][1],
                        // Greater than zero
                        -$unit['shipped'][1],
                    ));
                }

                if (0 !== bccomp(0, $shippedQty, 5)) {
                    $assignment['shipped'][1] += $shippedQty;
                    //$unit['shipped'][1] += $shippedQty;
                    $shippedDelta -= $shippedQty;
                }

                // Sold change
                if (0 < $soldDelta) {
                    // Credit case
                    $soldQty = max(0, $soldDelta); //min(
                    //    $soldDelta
                    //    // Lower than unit ordered + adjusted
                    //    //,$data['u_ordered'] + $data['u_adjusted'] - $unit['sold'][1]
                    //));
                } else {
                    // Debit case
                    $soldQty = min(0, max(
                        $soldDelta,
                        // Greater than assignment shipped
                        $assignment['shipped'][1] - $assignment['sold'][1]
                        // Greater than unit shipped
                        // TODO ,$unit['shipped'][1] - $unit['sold'][1]
                    ));
                }

                if (0 !== bccomp(0, $soldQty, 5)) {
                    $assignment['sold'][1] += $soldQty;
                    //$unit['sold'][1] += $soldQty;
                    $soldDelta -= $soldQty;
                }

                if (0 === bccomp(0, $soldDelta, 5) && 0 === bccomp(0, $shippedDelta, 5)) {
                    break;
                }
            } // End each assignments

            if (0 !== bccomp(0, $soldDelta, 5)) {
                // TODO Credit new stock unit
                throw new \RuntimeException(
                    "Order #$orderId Subject #$subjectId : remaining sold quantity: $soldDelta"
                );
            }
            if (0 !== bccomp(0, $shippedDelta, 5)) {
                // TODO Credit new stock unit
                throw new \RuntimeException(
                    "Order #$orderId Subject #$subjectId : remaining shipped quantity: $shippedDelta"
                );
            }
        } // End each items

        // Build actions
        $map = [
            'assignment' => $assignments,
            //'unit'       => $units,
        ];
        foreach ($map as $name => $data) {
            foreach ($data as $id => $datum) {
                $label = sprintf("Order #%d Subject #%d %s #%d", $datum['order'], $datum['subject'], ucfirst($name), $id);
                if (1 === bccomp($datum['shipped'][1], $datum['sold'][1], 5)) {
                    throw new \Exception($label . ": shipped is greater than sold.");
                }

                $sets = [];
                $params = [];
                foreach (['sold', 'shipped'] as $field) {
                    if (0 === bccomp($pre = $datum[$field][0], $post = $datum[$field][1], 5)) {
                        continue;
                    }
                    $label .= sprintf(", %s: %d -> %d", $field, $pre, $post);
                    $sets[] = $field . '_quantity=:' . $field;
                    $params[$field] = $post;
                }
                if (empty($sets)) {
                    continue;
                }

                /** @noinspection SqlResolve */
                $this->actions[] = new Fix(
                    $label,
                    "UPDATE commerce_stock_$name SET " . implode(',', $sets) . " WHERE id=:id LIMIT 1",
                    $params + ['id' => $id],
                    $name === 'unit' ? $id : null
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getMap(): array
    {
        return [
            'order_id'    => 'Order',
            'item_id'     => 'Item ID',
            'subject_id'  => 'Product',
            'sold_qty'    => 'Sold qty',
            'sold_sum'    => 'Items sum',
            'shipped_qty' => 'Shipped qty',
            'shipped_sum' => 'Shipments sum',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSql(): string
    {
        /** @noinspection SqlAggregates */
        return <<<SQL
SELECT 
    o.id AS order_id, 
    o.is_sample, 
    o.is_released,
    i1.id as item_id, 
    i1.subject_identifier AS subject_id, 
    SUM(a.sold_quantity) AS sold_qty, 
    SUM(a.shipped_quantity) AS shipped_qty,
    (
        i1.quantity * 
        IFNULL(i2.quantity, 1) * 
        IFNULL(i3.quantity, 1) * 
        IFNULL(i4.quantity, 1) * 
        IFNULL(i5.quantity, 1) *
        IFNULL(i6.quantity, 1)
    ) AS item_sum,
    IFNULL((
        SELECT SUM(line.quantity)
        FROM commerce_order_invoice_line AS line
        JOIN commerce_order_invoice AS invoice ON invoice.id=line.invoice_id
        WHERE line.order_item_id=i1.id
          AND invoice.credit=0
    ), 0) AS invoice_sum,
    IFNULL((
        SELECT SUM(line.quantity)
        FROM commerce_order_invoice_line AS line
        JOIN commerce_order_invoice AS invoice ON invoice.id=line.invoice_id
        WHERE line.order_item_id=i1.id
          AND invoice.credit=1
    ), 0) AS credit_sum,
    IFNULL((
        SELECT SUM(si.quantity)
        FROM commerce_order_shipment_item AS si
        JOIN commerce_order_shipment AS ss ON ss.id=si.shipment_id
        WHERE si.order_item_id=i1.id
          AND ss.is_return=0
          AND ss.state IN ('ready', 'shipped', 'completed')
    ), 0) AS shipment_sum,
    IFNULL((
        SELECT SUM(ri.quantity)
        FROM commerce_order_shipment_item AS ri
        JOIN commerce_order_shipment AS rs ON rs.id=ri.shipment_id
        WHERE ri.order_item_id=i1.id
          AND rs.is_return=1
          AND rs.state IN ('returned', 'completed')
    ), 0) AS return_sum
FROM commerce_stock_assignment AS a
JOIN commerce_order_item AS i1 ON i1.id=a.order_item_id
LEFT JOIN commerce_order_item AS i2 ON i2.id=i1.parent_id
LEFT JOIN commerce_order_item AS i3 ON i3.id=i2.parent_id
LEFT JOIN commerce_order_item AS i4 ON i4.id=i3.parent_id
LEFT JOIN commerce_order_item AS i5 ON i5.id=i4.parent_id
LEFT JOIN commerce_order_item AS i6 ON i6.id=i5.parent_id
JOIN commerce_order AS o ON (
    o.id=i1.order_id OR o.id=i2.order_id OR o.id=i3.order_id 
    OR o.id=i4.order_id OR o.id=i5.order_id OR o.id=i6.order_id
)
GROUP BY i1.id
ORDER BY o.id, i1.id;
SQL;
    }
}
