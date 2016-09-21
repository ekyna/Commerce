<?php

namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Common\Model\KeySubjectInterface;

/**
 * Class DefaultKeyGenerator
 * @package Ekyna\Component\Commerce\Order\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultKeyGenerator implements KeyGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(KeySubjectInterface $subject)
    {
        if (null !== $subject->getKey()) {
            return $this;
        }

        // TODO read last key from a file

        return $this;
    }
}
