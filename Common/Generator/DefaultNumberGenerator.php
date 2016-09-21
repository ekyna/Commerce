<?php

namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;

/**
 * Class DefaultNumberGenerator
 * @package Ekyna\Component\Commerce\Order\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultNumberGenerator implements NumberGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(NumberSubjectInterface $subject)
    {
        if (null !== $subject->getNumber()) {
            return $this;
        }

        // TODO read last number from a file

        return $this;
    }
}
