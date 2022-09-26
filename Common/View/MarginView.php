<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class MarginView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MarginView
{
    public function __construct(
        public readonly string $amount,
        public readonly string $percent
    ) {
    }
}
