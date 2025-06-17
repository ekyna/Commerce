<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Interface FollowerSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface FollowerSubjectInterface
{
    public function getFollowerCustomer(): ?CustomerInterface;

    public function setFollowerCustomer(?CustomerInterface $initiator): void;
}
