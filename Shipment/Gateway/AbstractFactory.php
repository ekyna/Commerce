<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Symfony\Component\Config\Definition;

/**
 * Class AbstractFactory
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var Definition\ArrayNode
     */
    private $configDefinition;


    /**
     * @inheritdoc
     */
    public function getConfigDefinition()
    {
        if (null !== $this->configDefinition) {
            return $this->configDefinition;
        }

        $treeBuilder = new Definition\Builder\TreeBuilder();

        $this->createConfigDefinition($treeBuilder->root('config'));

        return $this->configDefinition = $treeBuilder->buildTree();
    }

    /**
     * @inheritDoc
     */
    public function processGatewayConfig(array $config)
    {
        $processor = new Definition\Processor();

        return $processor->process($this->getConfigDefinition(), $config);
    }

    /**
     * Creates the gateway config definition.
     *
     * @param Definition\Builder\NodeDefinition $rootNode
     */
    abstract protected function createConfigDefinition(Definition\Builder\NodeDefinition $rootNode);
}
