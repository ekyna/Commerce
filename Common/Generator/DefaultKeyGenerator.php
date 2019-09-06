<?php

namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class DefaultKeyGenerator
 * @package Ekyna\Component\Commerce\Order\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultKeyGenerator extends AbstractGenerator implements GeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate($subject): string
    {
        // TODO read last key from a file

        throw new RuntimeException("Not yet implemented");
    }
}
