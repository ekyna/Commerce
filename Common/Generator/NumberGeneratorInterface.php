<?php


namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;

/**
 * Interface NumberGeneratorInterface
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NumberGeneratorInterface
{
    /**
     * Generates the subject number.
     *
     * @param NumberSubjectInterface $subject
     *
     * @return $this|NumberGeneratorInterface
     */
    public function generate(NumberSubjectInterface $subject);
}
