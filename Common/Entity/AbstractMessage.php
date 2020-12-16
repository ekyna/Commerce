<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class AbstractMessage
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\MessageTranslationInterface translate($locale = null, $create = false)
 */
abstract class AbstractMessage extends AbstractTranslatable implements Model\MessageInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var Model\MethodInterface
     */
    protected $method;


    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function setMethod(Model\MethodInterface $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->translate()->getContent();
    }

    /**
     * @inheritdoc
     */
    public function setContent($content)
    {
        $this->translate()->setContent($content);

        return $this;
    }
}
