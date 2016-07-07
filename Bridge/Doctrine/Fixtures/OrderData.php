<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class CustomerData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'order.yml',
            'class'    => 'Ekyna\Component\Commerce\Order\Entity\Order',
        ];
    }

    public function getOrder()
    {
        return 90;
    }
}
