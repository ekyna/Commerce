<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class SaleMentionEvent
 * @package Ekyna\Component\Commerce\Document\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleMentionEvent extends AbstractMentionEvent
{
    public function __construct(
        private readonly SaleInterface $sale,
        private readonly string        $type,
        private readonly ?string       $locale = null
    ) {
    }

    public function getSale(): SaleInterface
    {
        return $this->sale;
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
