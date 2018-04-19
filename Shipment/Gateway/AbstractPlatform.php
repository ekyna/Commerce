<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
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
     * @inheritdoc
     */
    public function supports(string $action)
    {
        return in_array($action, $this->getActions(), true);
    }

    /**
     * @inheritdoc
     */
    public function export(array $shipments)
    {
        $this->throwUnsupportedOperation('export');
    }

    /**
     * @inheritdoc
     */
    public function import($path)
    {
        $this->throwUnsupportedOperation('import');
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getConfigDefaults()
    {
        return [];
    }

    /**
     * @inheritdoc
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
     * Asserts that the given shipment is supported by this platform.
     *
     * @param ShipmentInterface $shipment
     *
     * @throws InvalidArgumentException
     */
    protected function assertShipmentPlatform(ShipmentInterface $shipment)
    {
        if ($shipment->getPlatformName() !== $this->getName()) {
            throw new InvalidArgumentException(sprintf(
                "Platform %s does not support shipment %s.",
                $this->getName(), $shipment->getNumber()
            ));
        }
    }

    /**
     * Throws an unsupported operation exception.
     *
     * @param string $operation
     *
     * @throws RuntimeException
     */
    protected function throwUnsupportedOperation($operation)
    {
        throw new RuntimeException(
            "The shipment platform '{$this->getName()}' does not support '$operation' operation."
        );
    }
}
