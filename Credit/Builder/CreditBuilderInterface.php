<?php

namespace Ekyna\Component\Commerce\Credit\Builder;

use Ekyna\Component\Commerce\Credit\Model\CreditInterface;

/**
 * Interface CreditBuilderInterface
 * @package Ekyna\Component\Commerce\Credit\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CreditBuilderInterface
{
    /**
     * Builds the credit.
     *
     * @param CreditInterface $credit
     */
    public function build(CreditInterface $credit);
}
