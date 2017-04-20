<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

/**
 * Class CartStates
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartStates
{
    public const STATE_NEW = 'new';
    public const STATE_ACCEPTED = 'accepted';


    public static function getStates(): array
    {
        return [
            CartStates::STATE_NEW,
            CartStates::STATE_ACCEPTED,
        ];
    }

    public static function isValidState(string $state): bool
    {
        return in_array($state, CartStates::getStates(), true);
    }

    /**
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
