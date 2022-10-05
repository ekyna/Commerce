<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Manager;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Interface CartItemManagerInterface
 * @package Ekyna\Component\Commerce\Cart\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface CartItemManagerInterface extends ResourceManagerInterface
{
    public function removeBySubjectIdentity(SubjectIdentity $identity): void;
}
