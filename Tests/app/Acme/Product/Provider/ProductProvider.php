<?php

declare(strict_types=1);

namespace Acme\Product\Provider;

use Ekyna\Bundle\ApiBundle\Action\SearchAction;
use Ekyna\Component\Commerce\Subject\Provider\AbstractSubjectProvider;

/**
 * Class ProductProvider
 * @package Acme\Commerce\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider extends AbstractSubjectProvider
{
    public function getSearchActionAndParameters(string $context): array
    {
        return [
            'action'     => SearchAction::class,
            'parameters' => [],
        ];
    }

    public static function getName(): string
    {
        return 'acme_product';
    }

    /**
     * @inheritDoc
     */
    public static function getLabel()
    {
        return 'Acme Product';
    }
}
