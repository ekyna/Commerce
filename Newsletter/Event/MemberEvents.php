<?php

namespace Ekyna\Component\Commerce\Newsletter\Event;

/**
 * Class MemberEvents
 * @package Ekyna\Component\Commerce\Newsletter\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class MemberEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.member.insert';
    const UPDATE      = 'ekyna_commerce.member.update';
    const DELETE      = 'ekyna_commerce.member.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.member.initialize';

    const PRE_CREATE  = 'ekyna_commerce.member.pre_create';
    const POST_CREATE = 'ekyna_commerce.member.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.member.pre_update';
    const POST_UPDATE = 'ekyna_commerce.member.post_update';

    const PRE_DELETE  = 'ekyna_commerce.member.pre_delete';
    const POST_DELETE = 'ekyna_commerce.member.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
