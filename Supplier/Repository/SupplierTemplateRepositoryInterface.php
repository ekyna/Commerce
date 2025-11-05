<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface SupplierTemplateRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<SupplierTemplateInterface>
 */
interface SupplierTemplateRepositoryInterface extends TranslatableRepositoryInterface
{
    public function findDefault(): ?SupplierTemplateInterface;
}
