<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BillOfMaterials
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterials extends Constraint
{
    public string $duplicateMessage = 'ekyna_commerce.bill_of_materials.duplicate';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
