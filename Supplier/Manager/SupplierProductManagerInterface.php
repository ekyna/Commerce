<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Manager;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Interface SupplierProductManagerInterface
 * @package Ekyna\Component\Commerce\Supplier\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SupplierProductManagerInterface extends ResourceManagerInterface
{
    public function clearSubjectIdentity(SubjectIdentity $identity): void;
}
