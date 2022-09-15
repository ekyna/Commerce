<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Common\Locking\LockChecker;

/**
 * Trait LockingHelperAware
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait LockCheckerAwareTrait
{
    protected readonly LockChecker $lockChecker;

    public function setLockChecker(LockChecker $lockingHelper): void
    {
        $this->lockChecker = $lockingHelper;
    }
}
