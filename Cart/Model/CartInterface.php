<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface CartInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartInterface extends SaleInterface
{
    /**
     * Returns the "expires at" datetime.
     *
     * @return \DateTime
     */
    public function getExpiresAt();

    /**
     * Sets the "expires at" datetime.
     *
     * @param \DateTime $expiresAt
     *
     * @return $this|CartInterface
     */
    public function setExpiresAt(\DateTime $expiresAt = null);
}
