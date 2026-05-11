<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class Icon
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Icon
{
    public function __construct(
        public readonly string $class,
        public readonly string $theme = 'default',
    ) {
    }

    public function html(): string
    {
        return "<i class=\"$this->class text-$this->theme\"></i>";
    }
}
