<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Ekyna\Component\Commerce\Cart\Manager\CartItemManagerInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;

/**
 * Class CartItemManager
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CartItemManager extends ResourceManager implements CartItemManagerInterface
{
    use RemoveBySubjectIdentityTrait;

    public function removeBySubjectIdentity(SubjectIdentity $identity): void
    {
        $this->doRemoveBySubjectIdentity($this->wrapped, $identity);
    }
}
