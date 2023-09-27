<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface MarginSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface MarginSubjectInterface
{
    /**
     * @return Margin
     */
    public function getMargin(): Margin;

    /**
     * @param Margin $margin
     *
     * @return MarginSubjectInterface
     */
    public function setMargin(Margin $margin): self;
}
