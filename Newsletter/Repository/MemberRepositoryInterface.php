<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Repository;

use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface MemberRepositoryInterface
 * @package Ekyna\Component\Commerce\Newsletter\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface MemberRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds one member by its email.
     *
     * @param string $email
     *
     * @return MemberInterface|null
     */
    public function findOneByEmail(string $email): ?MemberInterface;
}
