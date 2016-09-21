<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface HelperInterface
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface HelperInterface
{
    /**
     * Returns the subject from the order item.
     *
     * @param SaleItemInterface $item
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function resolve(SaleItemInterface $item);
}
