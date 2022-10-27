<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SaleItemEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEvent extends Event
{
    use AdjustmentDataTrait;

    private SaleItemInterface $item;
    private array             $data = [];

    public function __construct(SaleItemInterface $item)
    {
        $this->item = $item;
    }

    public function getItem(): SaleItemInterface
    {
        return $this->item;
    }

    public function setDatum(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function getDatum(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}
