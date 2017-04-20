<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Type;

/**
 * Class DoctrineBundleMapping
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineBundleMapping
{
    /**
     * Returns the doctrine ORM mapping config for the doctrine bundle (doctrine.dbal.types)
     *
     * @return array
     */
    public static function buildTypesConfiguration(): array
    {
        return [
            Type\OpeningHours::NAME => [
                'class' => Type\OpeningHours::class,
            ],
        ];
    }

    /**
     * Returns the doctrine ORM mapping config for the doctrine bundle (doctrine.orm.mappings)
     *
     * @return array
     */
    public static function buildMappingConfiguration(): array
    {
        return [
            'EkynaCommerce' => [
                'type'      => 'xml',
                'dir'       => realpath(__DIR__ . '/../ORM/Resources/mapping'),
                'is_bundle' => false,
                'prefix'    => 'Ekyna\Component\Commerce',
                'alias'     => 'EkynaCommerce',
            ],
        ];
    }
}
