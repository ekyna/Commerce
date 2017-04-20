<?php

namespace Acme\Product\Repository;

use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ProductRepository
 * @package Acme\Product\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends ResourceRepository implements SubjectRepositoryInterface
{

}
