<?php

namespace Ekyna\Component\Commerce\Newsletter\Repository;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface MemberRepositoryInterface
 * @package Ekyna\Component\Commerce\Newsletter\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method MemberInterface createNew()
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
