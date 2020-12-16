<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class CustomerGroupTranslation
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupTranslation extends AbstractTranslation implements CustomerGroupTranslationInterface
{
    /**
     * @var string
     */
    protected $title;


    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
