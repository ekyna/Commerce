<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class SaleTransformEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SaleTransformEvents
{
    public const PRE_COPY       = 'ekyna_commerce.sale.pre_copy';
    public const POST_COPY      = 'ekyna_commerce.sale.post_copy';
    public const PRE_TRANSFORM  = 'ekyna_commerce.sale.pre_transform';
    public const POST_TRANSFORM = 'ekyna_commerce.sale.post_transform';

    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}
