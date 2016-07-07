<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class TaxGroupData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'tax-group.yml',
            'class'    => 'Ekyna\Component\Commerce\Pricing\Entity\TaxGroup',
        ];
    }

    public function getOrder()
    {
        return 2;
    }
}
