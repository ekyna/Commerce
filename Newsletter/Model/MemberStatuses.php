<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class Status
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class MemberStatuses extends AbstractConstants
{
    public const SUBSCRIBED   = 'subscribed';
    public const UNSUBSCRIBED = 'unsubscribed';


    /**
     * @inheritDoc
     */
    public static function getConfig(): array
    {
        $prefix = 'ekyna_commerce.member.status.';

        return [
            self::SUBSCRIBED   => [$prefix . self::SUBSCRIBED,   'success'],
            self::UNSUBSCRIBED => [$prefix . self::UNSUBSCRIBED, 'default'],
        ];
    }
}
