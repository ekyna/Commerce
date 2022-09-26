<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class TaxView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxView extends AbstractView
{
    public function __construct(
        public readonly string $name,
        public readonly string $total
    ) {
    }
}
