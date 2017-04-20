<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Supplier\Repository\SupplierTemplateRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class SupplerTemplateRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateRepository extends TranslatableRepository implements SupplierTemplateRepositoryInterface
{

}
