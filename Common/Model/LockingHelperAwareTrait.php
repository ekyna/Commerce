<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Common\Locking\LockChecker;

/**
 * Trait LockingHelperAware
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait LockingHelperAwareTrait
{
    protected LockChecker $lockingHelper;


    public function setLockingHelper(LockChecker $lockingHelper): void
    {
        $this->lockingHelper = $lockingHelper;
    }
}
