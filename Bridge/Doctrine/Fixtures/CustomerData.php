<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class CustomerData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'customer.yml',
            'class'    => 'Ekyna\Component\Commerce\Customer\Entity\Customer',
        ];
    }

    public function getOrder()
    {
        return 2;
    }
}
