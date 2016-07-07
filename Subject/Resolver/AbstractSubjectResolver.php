<?php

namespace Ekyna\Component\Commerce\Subject\Resolver;

/**
 * Class AbstractSubjectResolver
 * @package Ekyna\Component\Commerce\Subject\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSubjectResolver implements SubjectResolverInterface
{
    /**
     * @var string
     */
    protected $itemClass;


    /**
     * @inheritdoc
     */
    public function setItemClass($class)
    {
        $this->itemClass = $class;

        return $this;
    }

    /**
     * Creates a new order item.
     *
     * @return \Ekyna\Component\Commerce\Order\Model\OrderItemInterface
     */
    protected function createNewItem()
    {
        return new $this->itemClass;
    }
}
