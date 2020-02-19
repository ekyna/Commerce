<?php

namespace Ekyna\Component\Commerce\Newsletter\Event;

/**
 * Class AudienceEvents
 * @package Ekyna\Component\Commerce\Newsletter\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class AudienceEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.audience.insert';
    const UPDATE      = 'ekyna_commerce.audience.update';
    const DELETE      = 'ekyna_commerce.audience.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.audience.initialize';

    const PRE_CREATE  = 'ekyna_commerce.audience.pre_create';
    const POST_CREATE = 'ekyna_commerce.audience.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.audience.pre_update';
    const POST_UPDATE = 'ekyna_commerce.audience.post_update';

    const PRE_DELETE  = 'ekyna_commerce.audience.pre_delete';
    const POST_DELETE = 'ekyna_commerce.audience.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
