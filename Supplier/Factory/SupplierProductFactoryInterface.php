<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Factory;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;

/**
 * Interface SupplierProductFactoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method SupplierProductInterface create()
 */
interface SupplierProductFactoryInterface extends ResourceFactoryInterface
{
    public function createWithSubjectAndSupplier(
        ?SupplierInterface $supplier,
        ?SubjectInterface  $subject
    ): SupplierProductInterface;
}
