<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PrintLabel
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PrintLabel extends AbstractAction
{
    const NAME = 'print_label';

    /**
     * @var string[]
     */
    private $labels;


    /**
     * Constructor.
     *
     * @param ServerRequestInterface $httpRequest
     * @param ShipmentInterface[]    $shipments
     */
    public function __construct(ServerRequestInterface $httpRequest, array $shipments = [])
    {
        parent::__construct($httpRequest, $shipments);

        $this->labels = [];
    }

    /**
     * Adds the label image data.
     *
     * @param string $data
     */
    public function addLabel($data)
    {
        $this->labels[] = $data;
    }

    /**
     * Returns the labels.
     *
     * @return string[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @inheritDoc
     */
    static public function getScopes()
    {
        return [static::SCOPE_PLATFORM, static::SCOPE_GATEWAY];
    }

    /**
     * @inheritDoc
     */
    static public function getName()
    {
        return static::NAME;
    }
}

