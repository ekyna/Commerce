<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Trait MentionSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait MentionSubjectTrait
{
    /** @var Collection<MentionInterface> */
    protected Collection $mentions;

    protected function initializeMentions(): void
    {
        $this->mentions = new ArrayCollection();
    }

    /**
     * @return Collection<MentionInterface>
     */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }
}
