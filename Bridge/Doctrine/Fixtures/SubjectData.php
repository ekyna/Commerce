<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

/**
 * Class SubjectData
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectData extends AbstractFixture
{
    protected function configure()
    {
        return [
            'filename' => 'subject.yml',
            'class'    => 'Ekyna\Component\Commerce\Subject\Entity\Subject',
        ];
    }

    public function getOrder()
    {
        return 80;
    }
}
