<?php

namespace Ekyna\Component\Commerce\Install;

use Doctrine\Common\Persistence\ObjectManager;

use Ekyna\Component\Commerce\Address\Entity\Country;
use Ekyna\Component\Commerce\Pricing\Entity\Currency;
use Symfony\Component\Intl\Data\Provider\CurrencyDataProvider;
use Symfony\Component\Intl\Intl;

/**
 * Class Installer
 * @package Ekyna\Component\Commerce\Install
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Installer
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var callable
     */
    private $log;


    /**
     * Constructor.
     *
     * @param ObjectManager $manager
     * @param mixed         $logger
     */
    public function __construct(ObjectManager $manager, $logger = null)
    {
        $this->manager = $manager;

        if (in_array('Symfony\Component\Console\Output\OutputInterface', class_implements($logger))) {
            $this->log = function($name) use ($logger) {
                /** @var \Symfony\Component\Console\Output\OutputInterface $logger */
                $logger->writeln(sprintf(
                    '- <comment>%s</comment> %s created.',
                    $name,
                    str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
                ));
            };
        } else {
            $this->log = function($name) {};
        }
    }

    /**
     * Installs the given countries and currencies by codes.
     *
     * @param array $countries  An array of country codes
     * @param array $currencies An array of currency codes
     *
     * @throws \Exception
     */
    public function install($countries = array('US'), $currencies = array('USD'))
    {
        $this->installCountries($countries);
        $this->installCurrencies($currencies);
    }

    /**
     * Installs the given countries by codes.
     *
     * @param array $codes
     *
     * @throws \Exception
     */
    public function installCountries($codes = array('US'))
    {
        if (empty($codes)) {
            throw new \Exception("Expected non empty array of enabled country codes.");
        }

        $countryNames = Intl::getRegionBundle()->getCountryNames(); // TODO locale
        asort($countryNames);

        $this->generate(Country::class, $countryNames, $codes);
    }

    /**
     * Installs the given currencies by codes.
     *
     * @param array $codes
     *
     * @throws \Exception
     */
    public function installCurrencies($codes = array('USD'))
    {
        if (empty($codes)) {
            throw new \Exception("Expected non empty array of currency codes.");
        }

        $currencyNames = Intl::getCurrencyBundle()->getCurrencyNames(); // TODO locale + sort by name
        asort($currencyNames);

        $this->generate(Currency::class, $currencyNames, $codes);
    }

    /**
     * Generates the entities.
     *
     * @param string $class
     * @param array $names
     * @param array $enabledCodes
     */
    private function generate($class, array $names, array $enabledCodes)
    {
        $enabledCodes = array_map(function($code) {
            return strtoupper($code);
        }, $enabledCodes);

        foreach ($names as $code => $name) {
            /** @var Country|Currency $entity */
            $entity = new $class();
            $entity
                ->setName($name)
                ->setCode($code)
                ->setEnabled(in_array($code, $enabledCodes));

            $this->manager->persist($entity);

            call_user_func($this->log, $name);
        }

        $this->manager->flush();
    }
}
