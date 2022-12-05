<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Event;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class SaleItemMentionEvent
 * @package Ekyna\Component\Commerce\Document\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemMentionEvent extends AbstractMentionEvent
{
    public function __construct(
        private readonly SaleItemInterface $item,
        private readonly string            $type,
        private readonly ?string           $locale = null
    ) {
    }

    public function getItem(): SaleItemInterface
    {
        return $this->item;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
