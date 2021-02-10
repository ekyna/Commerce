<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Common\Locking\LockChecker;

/**
 * Trait LockingHelperAware
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait LockingHelperAwareTrait
{
    /**
     * @var \Ekyna\Component\Commerce\Common\Locking\LockChecker
     */
    protected $lockingHelper;


    /**
     * Sets the locking helper.
     *
     * @param \Ekyna\Component\Commerce\Common\Locking\LockChecker $lockingHelper
     */
    public function setLockingHelper(LockChecker $lockingHelper): void
    {
        $this->lockingHelper = $lockingHelper;
    }
}
