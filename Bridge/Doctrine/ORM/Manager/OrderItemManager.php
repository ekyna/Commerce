<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Ekyna\Component\Commerce\Order\Manager\OrderItemManagerInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;

/**
 * Class OrderItemManager
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemManager extends ResourceManager implements OrderItemManagerInterface
{
    use ClearSubjectIdentityTrait;

    public function clearSubjectIdentity(SubjectIdentity $identity): void
    {
        $this->doClearSubjectIdentity($this->wrapped, $identity);
    }
}
