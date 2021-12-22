<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Releaser;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface ReleaserInterface
 * @package Ekyna\Component\Commerce\Payment\Releaser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ReleaserInterface
{
    /**
     * Releases fund from the given sale.
     *
     * @return bool Whether any payments has been changed
     */
    public function releaseFund(SaleInterface $sale): bool;
}
