<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Ekyna\Component\Commerce\Quote\Manager\QuoteItemManagerInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;

/**
 * Class QuoteItemManager
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemManager extends ResourceManager implements QuoteItemManagerInterface
{
    use RemoveBySubjectIdentityTrait;

    public function removeBySubjectIdentity(SubjectIdentity $identity): void
    {
        $this->doRemoveBySubjectIdentity($this->wrapped, $identity);
    }
}
