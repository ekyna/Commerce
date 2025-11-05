<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Helper\QuantityChangeHelper;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function min;

/**
 * Class QuantityResolver
 * @package Ekyna\Component\Commerce\Order\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuantityResolver
{
    public function __construct(
        private readonly PersistenceHelperInterface $persistenceHelper
    ) {
    }

    public function resolveSoldDelta(OrderItemInterface $item): Decimal
    {
        $helper = new QuantityChangeHelper($this->persistenceHelper);

        [$old, $new] = $helper->getTotalQuantityChangeSet($item);

        // Sale released change
        $sale = $item->getRootSale();
        $shippedOld = new Decimal(0);
        $shippedNew = new Decimal(0);
        $f = $t = false;
        if ($this->persistenceHelper->isChanged($sale, 'released')) {
            [$f, $t] = $this->persistenceHelper->getChangeSet($sale, 'released');
        } elseif ($item->getRootSale()->isReleased()) {
            $f = $t = true;
        }
        if ($f || $t) {
            /** @var AssignableInterface $item */
            foreach ($item->getStockAssignments() as $assignment) {
                if ($this->persistenceHelper->isChanged($assignment, 'shippedQuantity')) {
                    $cs = $this->persistenceHelper->getChangeSet($assignment, 'shippedQuantity');
                    $o = $cs[0] ?? new Decimal(0);
                    $n = $cs[1] ?? new Decimal(0);
                } else {
                    $o = $assignment->getShippedQuantity();
                    $n = $assignment->getShippedQuantity();
                }
                if ($f) {
                    $shippedOld += $o;
                }
                if ($t) {
                    $shippedNew += $n;
                }
            }

            if ($f) {
                $old = min($old, $shippedOld);
            }
            if ($t) {
                $new = min($new, $shippedNew);
            }
        }

        return $new->sub($old);
    }
}
