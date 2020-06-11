<?php

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
    /**
     * @var Collection|MentionInterface[]
     */
    protected $mentions;


    /**
     * Initializes the mentions.
     */
    protected function initializeMentions(): void
    {
        $this->mentions = new ArrayCollection();
    }

    /**
     * Returns the mentions.
     *
     * @return Collection|MentionInterface[]
     */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }
}
