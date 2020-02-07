<?php

namespace Ekyna\Component\Commerce\Common\Export;

use Symfony\Component\Intl\Intl;

/**
 * Class RegionProvider
 * @package Ekyna\Component\Commerce\Common\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegionProvider
{
    /** @see https://gist.github.com/henrik/1688572 */
    private const EUROPE = [
        // -----[ EU 28 ]-----
        "AT", // Austria
        "BE", // Belgium
        "BG", // Bulgaria
        "HR", // Croatia
        "CY", // Cyprus
        "CZ", // Czech Republic
        "DK", // Denmark
        "EE", // Estonia
        "FI", // Finland
        //"FR", // France
        "DE", // Germany
        "GR", // Greece
        "HU", // Hungary
        "IE", // Ireland, Republic of (EIRE)
        "IT", // Italy
        "LV", // Latvia
        "LT", // Lithuania
        "LU", // Luxembourg
        "MT", // Malta
        "NL", // Netherlands
        "PL", // Poland
        "PT", // Portugal
        "RO", // Romania
        "SK", // Slovakia
        "SI", // Slovenia
        "ES", // Spain
        "SE", // Sweden
        "GB", // United Kingdom (Great Britain)

        // -----[ Outermost Regions (OMR) ]------
        "GF", // French Guiana
        "GP", // Guadeloupe
        "MQ", // Martinique
        "ME", // Montenegro
        "YT", // Mayotte
        "RE", // Réunion
        "MF", // Saint Martin

        // -----[ Special Cases: Part of EU ]-----
        "GI", // Gibraltar
        "AX", // Åland Islands

        // -----[ Overseas Countries and Territories (OCT) ]-----
        "PM", // Saint Pierre and Miquelon
        "GL", // Greenland
        "BL", // Saint Bartelemey
        "SX", // Sint Maarten
        "AW", // Aruba
        "CW", // Curacao
        "WF", // Wallis and Futuna
        "PF", // French Polynesia
        "NC", // New Caledonia
        "TF", // French Southern Territories
        "AI", // Anguilla
        "BM", // Bermuda
        "IO", // British Indian Ocean Territory
        "VG", // Virgin Islands, British
        "KY", // Cayman Islands
        "FK", // Falkland Islands (Malvinas)
        "MS", // Montserrat
        "PN", // Pitcairn
        "SH", // Saint Helena
        "GS", // South Georgia and the South Sandwich Islands
        "TC", // Turks and Caicos Islands

        // -----[ Microstates ]-----
        "AD", // Andorra
        "LI", // Liechtenstein
        "MC", // Monaco
        "SM", // San Marino
        "VA", // Vatican City

        // -----[ Other ]-----
        "JE", // Jersey
        "GG", // Guernsey
    ];

    /**
     * Returns the regions.
     *
     * @return array
     */
    public function getRegions(): array
    {
        $all = array_keys(Intl::getRegionBundle()->getCountryNames());

        $world = array_filter($all, function ($code) {
            return ($code !== 'FR') && !in_array($code, self::EUROPE);
        });

        return [
            'France' => ['FR'],
            'Europe' => self::EUROPE,
            'World'  => $world,
        ];
    }
}
