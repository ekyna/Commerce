<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Model;

use Ekyna\Component\Resource\Enum\ColorInterface;
use Ekyna\Component\Resource\Enum\LabelInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function in_array;
use function Symfony\Component\Translation\t;

/**
 * Enum POState
 * @package Ekyna\Component\Commerce\Manufacture\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
enum POState: string implements LabelInterface, ColorInterface
{
    case NEW       = 'new';
    case SCHEDULED = 'scheduled';
    case DONE      = 'done';
    case CANCELED  = 'canceled';

    public function label(): TranslatableInterface
    {
        return t('state.' . $this->value, [], 'EkynaUi');
    }

    public function color(): string
    {
        return match ($this) {
            POState::NEW       => 'brown',
            POState::SCHEDULED => 'orange',
            POState::DONE      => 'teal',
            POState::CANCELED  => 'grey',
        };
    }

    public static function isStockableState(ProductionOrderInterface|string|self $state): bool
    {
        if ($state instanceof ProductionOrderInterface) {
            $state = $state->getState();
        } elseif (is_string($state)) {
            $state = self::tryFrom($state);
        }

        return in_array($state, [self::SCHEDULED, self::DONE], true);
    }

    /**
     * @param array{0: string, 1: string} $stateCS
     */
    public static function hasChangedFromStockable(array $stateCS): bool
    {
        return POState::isStockableState($stateCS[0])
            && !POState::isStockableState($stateCS[1]);
    }

    /**
     * @param array{0: string, 1: string} $stateCS
     */
    public static function hasChangedToStockable(array $stateCS): bool
    {
        return !POState::isStockableState($stateCS[0])
            && POState::isStockableState($stateCS[1]);
    }
}
