<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section\Model;

use Ekyna\Component\Commerce\Common\Model\Margin;

/**
 * Class OrderData
 * @package Ekyna\Component\Commerce\Report\Section\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderData
{
    public function __construct(
        public readonly Margin $grossMargin,
        public readonly Margin $commercialMargin,
    ) {
    }
}
