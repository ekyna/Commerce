<?php

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class SaleTransformEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SaleTransformEvents
{
    const PRE_COPY       = 'ekyna_commerce.sale.pre_copy';
    const POST_COPY      = 'ekyna_commerce.sale.post_copy';
    const PRE_TRANSFORM  = 'ekyna_commerce.sale.pre_transform';
    const POST_TRANSFORM = 'ekyna_commerce.sale.post_transform';


    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}
