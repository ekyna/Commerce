<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class TaxRuleData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'tax-rule.yml',
            'class'    => 'Ekyna\Component\Commerce\Pricing\Entity\TaxRule',
        ];
    }

    public function getOrder()
    {
        return 3;
    }
}
