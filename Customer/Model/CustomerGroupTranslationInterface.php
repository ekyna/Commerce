<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface CustomerGroupTranslationInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerGroupTranslationInterface extends TranslationInterface
{
    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return $this|CustomerGroupTranslationInterface
     */
    public function setTitle($title);
}
