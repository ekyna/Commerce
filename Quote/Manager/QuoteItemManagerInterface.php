<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Manager;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Interface QuoteItemManagerInterface
 * @package Ekyna\Component\Commerce\Quote\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface QuoteItemManagerInterface extends ResourceManagerInterface
{
    public function removeBySubjectIdentity(SubjectIdentity $identity): void;
}
