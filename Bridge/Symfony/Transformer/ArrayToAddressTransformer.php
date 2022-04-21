<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Transformer;

use Ekyna\Component\Commerce\Common\Transformer\ArrayToAddressTransformer as BaseTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ArrayToAddressTransformer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Transformer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ArrayToAddressTransformer extends BaseTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        return parent::transformArray($value);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        return parent::transformAddress($value);
    }
}
