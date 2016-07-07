<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class CurrencyData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'currency.yml',
            'class'    => 'Ekyna\Component\Commerce\Pricing\Entity\Currency',
        ];
    }

    public function getOrder()
    {
        return 1;
    }
}
