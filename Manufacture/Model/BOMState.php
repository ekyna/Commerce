<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Model;

use Ekyna\Component\Resource\Enum\ColorInterface;
use Ekyna\Component\Resource\Enum\LabelInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Enum BOMState
 * @package Ekyna\Component\Commerce\Manufacture\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
enum BOMState: string implements LabelInterface, ColorInterface
{
    case DRAFT     = 'draft';
    case VALIDATED = 'validated';
    case ARCHIVED  = 'archived';

    public function label(): TranslatableInterface
    {
        return t('state.' . $this->value, [], 'EkynaUi');
    }

    public function color(): string
    {
        return match ($this) {
            BOMState::DRAFT     => 'brown',
            BOMState::VALIDATED => 'teal',
            BOMState::ARCHIVED  => 'grey',
        };
    }
}
