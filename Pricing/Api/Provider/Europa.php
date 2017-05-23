<?php

namespace Ekyna\Component\Commerce\Pricing\Api\Provider;

use Ekyna\Component\Commerce\Pricing\Api\VatNumberResult;

/**
 * Class Europa
 * @package Ekyna\Component\Commerce\Pricing\Api\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @see http://ec.europa.eu/taxation_customs/vies/?locale=fr
 */
class Europa implements VatNumberValidatorInterface
{
    const SERVICE_ID = 'ekyna_commerce.pricing.api.provider.europa';

    const ENDPOINT = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    const COUNTRY_CODES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'EL', 'HU', 'IE',
        'IT', 'LV', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'BG',
    ];

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var \SoapClient;
     */
    private $client;


    /**
     * Constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * @inheritDoc
     */
    public function validateVatNumber($vatNumber)
    {
        $vatNumber = preg_replace('~[^A-Z0-9]+~', '', strtoupper($vatNumber));

        // Abort if the country code is not supported
        $country = substr($vatNumber, 0, 2);
        if (!in_array($country, static::COUNTRY_CODES, true)) {
            return null;
        }

        if ($client = $this->getClient()) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $response = $client->checkVat([
                    'countryCode' => $country,
                    'vatNumber'   => substr($vatNumber, 2),
                ]);

                return new VatNumberResult(
                    $response->valid,
                    $response->countryCode,
                    $response->vatNumber,
                    $response->name,
                    $response->address
                );
            } catch (\SoapFault $oExcept) {
                if ($this->debug) {
                    @trigger_error('Failed to retrieve the VAT details: ' . $oExcept->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Returns the soap client.
     *
     * @return \SoapClient
     */
    private function getClient()
    {
        if (null !== $this->client) {
            return $this->client;
        }

        try {
            return $this->client = new \SoapClient(static::ENDPOINT);
        } catch (\SoapFault $oExcept) {
            if ($this->debug) {
                @trigger_error('Failed to connect to the europa web service: ' . $oExcept->getMessage());
            }
        }

        return $this->client = null;
    }
}
