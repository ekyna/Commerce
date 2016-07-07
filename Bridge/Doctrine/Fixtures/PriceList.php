<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class TaxRuleData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceList extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'price-list.yml',
            'class'    => 'Ekyna\Component\Commerce\Pricing\Entity\PriceList',
        ];
    }

    public function getOrder()
    {
        return 3;
    }
}
