<?php

namespace Acme\Product\Bridge;

/**
 * Class DoctrineBridge
 * @package Acme\Product\Bridge
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineBridge
{
    /**
     * Returns the config to may be append to doctrine.orm.mappings
     *
     * @return array
     */
    static function getORMMappingConfiguration()
    {
        return [
            'type'      => 'xml',
            'dir'       => realpath(__DIR__ . '/../Resources/config/doctrine'),
            'is_bundle' => false,
            'prefix'    => 'Acme\Product',
            'alias'     => 'AcmeProduct',
        ];
    }
}
