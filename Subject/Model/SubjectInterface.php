<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SubjectInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectInterface extends ResourceInterface
{
    /**
     * Returns the provider name.
     *
     * @return string
     */
    static public function getProviderName();
}
