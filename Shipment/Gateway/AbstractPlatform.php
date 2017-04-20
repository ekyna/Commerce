<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Symfony\Component\Config\Definition;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * Class AbstractPlatform
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPlatform implements PlatformInterface
{
    protected GatewayRegistryInterface $registry;

    private ?Definition\ArrayNode $configDefinition = null;


    public function supports(string $action): bool
    {
        return in_array($action, $this->getActions(), true);
    }

    public function export(array $shipments): string
    {
        $this->throwUnsupportedOperation('export');
    }

    public function import(string $path): bool
    {
        $this->throwUnsupportedOperation('import');
    }

    public function setRegistry(GatewayRegistryInterface $registry): void
    {
        $this->registry = $registry;
    }

    public function getConfigDefinition(): NodeInterface
    {
        if (null !== $this->configDefinition) {
            return $this->configDefinition;
        }

        $treeBuilder = new Definition\Builder\TreeBuilder('config');

        $this->createConfigDefinition($treeBuilder->getRootNode());

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        return $this->configDefinition = $treeBuilder->buildTree();
    }

    public function getConfigDefaults(): array
    {
        return [];
    }

    public function processGatewayConfig(array $config): array
    {
        $processor = new Definition\Processor();

        return $processor->process($this->getConfigDefinition(), ['config' => $config]);
    }

    /**
     * Creates the gateway config definition.
     */
    protected function createConfigDefinition(Definition\Builder\ArrayNodeDefinition $rootNode): void
    {

    }

    /**
     * Asserts that the given shipment is supported by this platform.
     *
     * @param ShipmentInterface $shipment
     *
     * @throws InvalidArgumentException
     */
    protected function assertShipmentPlatform(ShipmentInterface $shipment): void
    {
        if ($shipment->getPlatformName() !== $this->getName()) {
            throw new InvalidArgumentException(sprintf(
                'Platform %s does not support shipment %s.',
                $this->getName(),
                $shipment->getNumber()
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
    protected function throwUnsupportedOperation(string $operation): void
    {
        throw new RuntimeException(
            "The shipment platform '{$this->getName()}' does not support '$operation' operation."
        );
    }
}
