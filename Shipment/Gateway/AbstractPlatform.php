<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Symfony\Component\Config\Definition;

/**
 * Class AbstractPlatform
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPlatform implements PlatformInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var Definition\ArrayNode
     */
    private $configDefinition;


    /**
     * @inheritDoc
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

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

        return $processor->process($this->getConfigDefinition(), ['config' => $config]);
    }

    /**
     * Creates the gateway config definition.
     *
     * @param Definition\Builder\NodeDefinition $rootNode
     */
    protected function createConfigDefinition(Definition\Builder\NodeDefinition $rootNode)
    {

    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function supports(ActionInterface $action)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getActions()
    {
        return [];
    }
}
