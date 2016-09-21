<?php


namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Common\Model\KeySubjectInterface;

/**
 * Interface KeyGeneratorInterface
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface KeyGeneratorInterface
{
    /**
     * Generates the subject key.
     *
     * @param KeySubjectInterface $subject
     *
     * @return $this|KeyGeneratorInterface
     */
    public function generate(KeySubjectInterface $subject);
}
