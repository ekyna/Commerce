<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Interface InitiatorSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface InitiatorSubjectInterface
{
    public function getInitiatorCustomer(): ?CustomerInterface;

    public function setInitiatorCustomer(?CustomerInterface $initiator): void;
}
