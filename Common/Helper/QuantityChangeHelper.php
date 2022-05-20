<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Helper;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class QuantityChangeHelper
 * @package Ekyna\Component\Commerce\Common\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuantityChangeHelper
{
    private PersistenceHelperInterface $persistenceHelper;

    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @return array[Decimal, Decimal]
     */
    public function getTotalQuantityChangeSet(SaleItemInterface $item): array
    {
        // Own item quantity changes
        [$old, $new] = $this->getQuantityChangeSet($item);

        // Parent items quantity changes
        $parent = $item;
        while ($parent = $parent->getParent()) {
            [$parentOld, $parentNew] = $this->getQuantityChangeSet($parent);

            $old *= $parentOld;
            $new *= $parentNew;
        }

        if ($old->equals($new)) {
            return [];
        }

        return [$old, $new];
    }

    private function getQuantityChangeSet(SaleItemInterface $item): array
    {
        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            $cs = $this->persistenceHelper->getChangeSet($item, 'quantity');
            $old = $cs[0] ?? new Decimal(0);
            $new = $cs[1] ?? new Decimal(0);
        } else {
            $old = $item->getQuantity();
            $new = $item->getQuantity();
        }

        return [$old, $new];
    }
}
