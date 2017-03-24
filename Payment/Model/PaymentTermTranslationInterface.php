<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface PaymentTermTranslationInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentTermTranslationInterface extends TranslationInterface
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
     * @return $this|PaymentTermTranslationInterface
     */
    public function setTitle($title);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|PaymentTermTranslationInterface
     */
    public function setDescription($description);
}
