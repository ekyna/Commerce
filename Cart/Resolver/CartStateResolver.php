<?php

namespace Ekyna\Component\Commerce\Cart\Resolver;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class CartStateResolver
 * @package Ekyna\Component\Commerce\Cart\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(StateSubjectInterface $subject)
    {
        if (!$subject instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface.");
        }

        // TODO: Implement resolve() method.

        return $subject->getState();
    }
}
