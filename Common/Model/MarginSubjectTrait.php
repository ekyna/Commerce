<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait MarginSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait MarginSubjectTrait
{
    protected Margin $margin;

    protected function initializeMargin(): void
    {
        $this->margin = new Margin();
    }

    /**
     * @return Margin
     */
    public function getMargin(): Margin
    {
        return $this->margin;
    }

    /**
     * @param Margin $margin
     *
     * @return MarginSubjectInterface
     */
    public function setMargin(Margin $margin): MarginSubjectInterface
    {
        $this->margin = $margin;

        return $this;
    }
}
