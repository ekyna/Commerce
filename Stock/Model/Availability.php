<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Decimal\Decimal;

/**
 * Class Availability
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Availability
{
    private string  $overflowMessage;
    private Decimal $minimumQuantity;
    private string  $minimumMessage;
    private Decimal $maximumQuantity;
    private string  $maximumMessage;
    private Decimal $availableQuantity;
    private string  $availableMessage;
    private Decimal $resupplyQuantity;
    private string  $resupplyMessage;

    public function __construct(
        string  $overflowMessage,
        Decimal $minimumQuantity,
        string  $minimumMessage,
        Decimal $maximumQuantity,
        string  $maximumMessage,
        Decimal $availableQuantity,
        string  $availableMessage,
        Decimal $resupplyQuantity,
        string  $resupplyMessage
    ) {
        $this->overflowMessage = $overflowMessage;
        $this->minimumQuantity = $minimumQuantity;
        $this->minimumMessage = $minimumMessage;
        $this->maximumQuantity = $maximumQuantity;
        $this->maximumMessage = $maximumMessage;
        $this->availableQuantity = $availableQuantity;
        $this->availableMessage = $availableMessage;
        $this->resupplyQuantity = $resupplyQuantity;
        $this->resupplyMessage = $resupplyMessage;
    }

    /**
     * Returns the messages for the given quantity.
     */
    public function getMessagesForQuantity(Decimal $quantity): array
    {
        $messages = [];

        if ($quantity < $this->minimumQuantity) {
            $messages[] = $this->minimumMessage;
        } elseif (0 < $this->maximumQuantity && $quantity > $this->maximumQuantity) {
            $messages[] = $this->maximumMessage;
        } else {
            if (null !== $this->availableMessage) {
                $messages[] = $this->availableMessage;
            }

            if ($quantity > $this->availableQuantity) {
                if (null !== $this->resupplyMessage) {
                    $messages[] = $this->resupplyMessage;
                    if ($quantity > $this->availableQuantity + $this->resupplyQuantity) {
                        $messages[] = $this->overflowMessage;
                    }
                } else {
                    $messages[] = $this->overflowMessage;
                }
            }
        }

        if (empty($messages)) {
            $messages[] = $this->overflowMessage;
        }

        return $messages;
    }

    public function isAvailableForQuantity(Decimal $quantity): bool
    {
        if ($quantity < $this->minimumQuantity) {
            return false;
        } elseif ($quantity > $this->availableQuantity + $this->resupplyQuantity) {
            return false;
        }

        return true;
    }

    public function getOverflowMessage(): string
    {
        return $this->overflowMessage;
    }

    public function getMinimumQuantity(): Decimal
    {
        return $this->minimumQuantity;
    }

    public function getMinimumMessage(): ?string
    {
        return $this->minimumMessage;
    }

    public function getMaximumQuantity(): Decimal
    {
        return $this->maximumQuantity;
    }

    public function getMaximumMessage(): ?string
    {
        return $this->maximumMessage;
    }

    public function getAvailableQuantity(): Decimal
    {
        return $this->availableQuantity;
    }

    public function getAvailableMessage(): ?string
    {
        return $this->availableMessage;
    }

    public function getResupplyQuantity(): Decimal
    {
        return $this->resupplyQuantity;
    }

    public function getResupplyMessage(): ?string
    {
        return $this->resupplyMessage;
    }

    public function toArray(): array
    {
        return [
            'o_msg'   => $this->overflowMessage,
            'min_qty' => $this->minimumQuantity,
            'min_msg' => $this->minimumMessage,
            'max_qty' => INF === $this->maximumQuantity ? 'INF' : $this->maximumQuantity,
            'max_msg' => $this->maximumMessage,
            'a_qty'   => INF === $this->availableQuantity ? 'INF' : $this->availableQuantity,
            'a_msg'   => $this->availableMessage,
            'r_qty'   => $this->resupplyQuantity,
            'r_msg'   => $this->resupplyMessage,
        ];
    }
}
