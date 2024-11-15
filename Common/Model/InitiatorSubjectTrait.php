<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Trait InitiatorSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait InitiatorSubjectTrait
{
    protected ?CustomerInterface $initiatorCustomer = null; // TODO Rename to 'business introducer'

    public function getInitiatorCustomer(): ?CustomerInterface
    {
        return $this->initiatorCustomer;
    }

    public function setInitiatorCustomer(?CustomerInterface $initiatorCustomer): void
    {
        $this->initiatorCustomer = $initiatorCustomer;
    }
}
