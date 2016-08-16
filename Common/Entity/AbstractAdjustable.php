<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class AbstractAdjustable
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAdjustable implements AdjustableInterface
{
    /**
     * @var ArrayCollection|AdjustmentInterface[]
     */
    protected $adjustments;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->adjustments = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustments($type = null)
    {
        if (null !== $type) {
            $this->validateAdjustmentType($type);

            return $this->getAdjustments($type)->count();
        }

        return 0 < $this->adjustments->count();
    }

    /**
     * @inheritdoc
     */
    public function getAdjustments($type = null)
    {
        if (null !== $type) {
            $this->validateAdjustmentType($type);

            return $this
                ->adjustments
                ->filter(function(AdjustmentInterface $a) use ($type) {
                    return $a->getType() === $type;
                });
        }

        return $this->adjustments;
    }

    /**
     * Validates the adjustment type.
     *
     * @param string $type
     *
     * @throws InvalidArgumentException
     */
    private function validateAdjustmentType($type)
    {
        if (!AdjustmentTypes::isValidType($type)) {
            throw new InvalidArgumentException('Invalid adjustment type');
        }
    }
}
