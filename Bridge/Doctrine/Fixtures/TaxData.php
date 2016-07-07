<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class TaxData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'tax.yml',
            'class'    => 'Ekyna\Component\Commerce\Pricing\Entity\Tax',
        ];
    }

    public function getOrder()
    {
        return 2;
    }
}
