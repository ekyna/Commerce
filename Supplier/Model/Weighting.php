<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Exception\RuntimeException;

use function array_key_exists;
use function spl_object_id;

/**
 * Class Weighting
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class Weighting
{
    /**
     * @var array<string, ItemWeighting>
     */
    private array $items = [];

    public function __construct(
        public readonly bool   $missingWeight,
        public readonly bool   $missingPrice,
    ) {
    }

    public function addItem(SupplierOrderItemInterface $item, ItemWeighting $weighting): void
    {
        $weighting->setOrderWeighting($this);

        $this->items[spl_object_id($item)] = $weighting;
    }

    public function getItem(SupplierOrderItemInterface $item): ItemWeighting
    {
        $key = spl_object_id($item);

        if (!array_key_exists($key, $this->items)) {
            throw new RuntimeException();
        }

        return $this->items[$key];
    }
}
