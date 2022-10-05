<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Manager;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Interface OrderItemManagerInterface
 * @package Ekyna\Component\Commerce\Order\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemManagerInterface extends ResourceManagerInterface
{
    public function clearSubjectIdentity(SubjectIdentity $identity): void;
}
