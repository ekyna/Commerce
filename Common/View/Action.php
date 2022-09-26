<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class Action
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Action
{
    public function __construct(
        public readonly string $path,
        public readonly string $icon,
        public readonly array $attributes
    ) {
    }
}
