<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AbstractMentionEvent
 * @package Ekyna\Component\Commerce\Document\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractMentionEvent extends Event
{
    /** @var array<int, string> */
    private array $mentions = [];

    public function addMention(string $mention): void
    {
        $this->mentions[] = $mention;
    }

    public function getMentions(): array
    {
        return $this->mentions;
    }
}
