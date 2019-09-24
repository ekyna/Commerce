<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMethodTranslation;

/**
 * Class PaymentMethodTranslation
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodTranslation extends AbstractMethodTranslation
{
    /**
     * @var string
     */
    private $mention;

    /**
     * @var string
     */
    private $footer;


    /**
     * Returns the mention.
     *
     * @return string
     */
    public function getMention(): ?string
    {
        return $this->mention;
    }

    /**
     * Sets the mention.
     *
     * @param string $mention
     *
     * @return PaymentMethodTranslation
     */
    public function setMention(string $mention = null): PaymentMethodTranslation
    {
        $this->mention = $mention;

        return $this;
    }

    /**
     * Returns the footer.
     *
     * @return string
     */
    public function getFooter(): ?string
    {
        return $this->footer;
    }

    /**
     * Sets the footer.
     *
     * @param string $footer
     *
     * @return PaymentMethodTranslation
     */
    public function setFooter(string $footer = null): PaymentMethodTranslation
    {
        $this->footer = $footer;

        return $this;
    }
}
