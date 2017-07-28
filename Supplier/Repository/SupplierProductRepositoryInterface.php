<?php

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;

/**
 * Interface SupplierProductRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierProductRepositoryInterface
{
    /**
     * Finds the supplier product related to the given subject.
     *
     * @param SubjectInterface $subject
     *
     * @return SupplierProductInterface[]
     */
    public function findBySubject(SubjectInterface $subject);
}
