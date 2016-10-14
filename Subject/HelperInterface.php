<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

/**
 * Interface HelperInterface
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface HelperInterface
{
    /**
     * Returns the subject from the relative.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function resolve(SubjectRelativeInterface $relative);
}
