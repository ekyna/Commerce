<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Trait FollowerSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait FollowerSubjectTrait
{
    protected ?CustomerInterface $followerCustomer = null;

    public function getFollowerCustomer(): ?CustomerInterface
    {
        return $this->followerCustomer;
    }

    public function setFollowerCustomer(?CustomerInterface $followerCustomer): void
    {
        $this->followerCustomer = $followerCustomer;
    }
}
