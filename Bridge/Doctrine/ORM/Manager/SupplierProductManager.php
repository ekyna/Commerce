<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Supplier\Manager\SupplierProductManagerInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;

/**
 * Class SupplierProductItemManager
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductManager extends ResourceManager implements SupplierProductManagerInterface
{
    use ClearSubjectIdentityTrait;

    public function clearSubjectIdentity(SubjectIdentity $identity): void
    {
        $this->doClearSubjectIdentity($this->wrapped, $identity);
    }
}
