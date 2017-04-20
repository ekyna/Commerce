<?php

namespace Ekyna\Component\Commerce\Pricing\Api\Provider;

use Ekyna\Component\Commerce\Pricing\Api\VatNumberResult;

/**
 * Class VatApi
 * @package Ekyna\Component\Commerce\Pricing\Api\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @see https://vatapi.com
 */
class VatApi implements VatNumberValidatorInterface
{
    const SERVICE_ID = 'ekyna_commerce.provider.pricing_api.vat_api';

    const ENDPOINT = 'https://vatapi.com/v1?';

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
    public function validateVatNumber($vatNumber)
    {
        $url = sprintf(
            '%s/vat-number-check?vatid=%s',
            static::ENDPOINT,
            $vatNumber
        );

        if (function_exists('curl_version')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Apikey: '.$this->accessKey));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);

        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 && $code >= 300) {
            return null;
        }

        $result = json_decode($content, true);
        if (empty($result)) {
            return null;
        }

        return new VatNumberResult(
            $result['valid'],
            $result['countryCode'],
            $result['vatNumber'],
            $result['name'],
            $result['address']
        );
    }
}
