<?php

namespace Ekyna\Component\Commerce\Customer\Validator\Provider;

use Ekyna\Component\Commerce\Customer\Validator\VatResult;

/**
 * Class VatLayer
 * @package Ekyna\Component\Commerce\Customer\Validator\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @see https://vatlayer.com/quickstart
 */
class VatLayer implements ProviderInterface
{
    const SERVICE_ID = 'ekyna_commerce.customer.validator.vat_number.provider.vat_layer';

    /**
     * @var string
     */
    private $accessKey;

    /**
     * @var bool
     */
    private $debug;


    /**
     * Constructor.
     *
     * @param string $accessKey
     * @param bool   $debug
     */
    public function __construct($accessKey, $debug = false)
    {
        $this->accessKey = $accessKey;
        $this->debug = $debug;
    }

    /**
     * @inheritDoc
     */
    public function validate($vatNumber)
    {
        // TODO: Implement validate() method.
        // Use curl ...
        throw new \Exception('Not yet supported.');
    }
}
