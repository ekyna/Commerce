<?php

namespace Ekyna\Component\Commerce\Pricing\Api\Provider;

use Ekyna\Component\Commerce\Pricing\Api\VatNumberResult;

/**
 * Class VatLayer
 * @package Ekyna\Component\Commerce\Pricing\Api\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @see https://vatlayer.com/documentation
 */
class VatLayer implements VatNumberValidatorInterface
{
    const SERVICE_ID = 'ekyna_commerce.provider.pricing_api.vat_layer';

    const ENDPOINT = 'http://apilayer.net/api';

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
            '%s/validate?access_key=%s&vat_number=%s',
            static::ENDPOINT,
            $this->accessKey,
            $vatNumber
        );

        if (function_exists('curl_version')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 70);

            $content = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code < 200 && $code >= 300) {
                return null;
            }
        } elseif (empty($content = @file_get_contents($url))) {
            return null;
        }

        $result = json_decode($content, true);
        if (empty($result)) {
            return null;
        }

        return new VatNumberResult(
            $result['valid'],
            $result['country_code'],
            $result['vat_number'],
            $result['company_name'],
            $result['company_address']
        );
    }
}
