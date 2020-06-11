<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface MentionSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MentionSubjectInterface
{
    /**
     * Returns the mentions.
     *
     * @return Collection|MentionInterface[]
     */
    public function getMentions(): Collection;
}
