<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class TotalView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TotalView extends AbstractView
{
    public function __construct(
        public readonly string $base,
        public readonly string $adjustment,
        public readonly string $total
    ) {
    }
}
