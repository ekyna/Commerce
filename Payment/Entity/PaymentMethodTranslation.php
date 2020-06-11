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
    private $notice;

    /**
     * @var string
     */
    private $footer;


    /**
     * Returns the notice.
     *
     * @return string
     */
    public function getNotice(): ?string
    {
        return $this->notice;
    }

    /**
     * Sets the notice.
     *
     * @param string $notice
     *
     * @return PaymentMethodTranslation
     */
    public function setNotice(string $notice = null): PaymentMethodTranslation
    {
        $this->notice = $notice;

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
