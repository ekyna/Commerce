<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class OrderStateResolver
 * @package Ekyna\Component\Commerce\Order\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(StateSubjectInterface $subject)
    {
        if (!$subject instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface.");
        }

        // TODO: Implement resolve() method.
    }
}
