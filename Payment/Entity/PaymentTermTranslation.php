<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Payment\Model\PaymentTermTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class PaymentTermTranslation
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermTranslation extends AbstractTranslation implements PaymentTermTranslationInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;


    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
