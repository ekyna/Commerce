<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class CustomerGroupData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'customer-group.yml',
            'class'    => 'Ekyna\Component\Commerce\Customer\Entity\CustomerGroup',
        ];
    }

    public function getOrder()
    {
        return 1;
    }
}
