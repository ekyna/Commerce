<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class CountryData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'country.yml',
            'class'    => 'Ekyna\Component\Commerce\Address\Entity\Country',
        ];
    }

    public function getOrder()
    {
        return 1;
    }
}
