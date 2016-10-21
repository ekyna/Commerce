<?php

namespace Ekyna\Component\Commerce\Install;

use Doctrine\Common\Persistence\ObjectManager;

use Ekyna\Component\Commerce\Common\Entity\Country;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Pricing\Entity\TaxGroup;
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
            $this->log = function($name, $result) use ($logger) {
                /** @var \Symfony\Component\Console\Output\OutputInterface $logger */
                $logger->writeln(sprintf(
                    '- <comment>%s</comment> %s %s.',
                    $name,
                    str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT),
                    $result
                ));
            };
        } else {
            $this->log = function($name, $result) {};
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
        $this->installTaxGroups();
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

        $countryNames = Intl::getRegionBundle()->getCountryNames();
        asort($countryNames);

        // TODO class parameter
        $this->generate(Country::class, $countryNames, $codes, $codes[0]);
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

        $currencyNames = Intl::getCurrencyBundle()->getCurrencyNames();
        asort($currencyNames);

        // TODO class parameter
        $this->generate(Currency::class, $currencyNames, $codes, $codes[0]);
    }

    /**
     * Installs the default tax group.
     */
    public function installTaxGroups()
    {
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $repository */
        $repository = $this->manager->getRepository(TaxGroup::class);

        $name = 'Default tax group';

        $result = 'already exists';
        if (null === $repository->findOneBy(['default' => true])) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface $taxGroup */
            $taxGroup = $repository->createNew();
            $taxGroup
                ->setName($name)
                ->setDefault(true);

            $this->manager->persist($taxGroup);
            $this->manager->flush();

            $result = 'done';
        }

        call_user_func($this->log, $name, $result);
    }

    /**
     * Generates the entities.
     *
     * @param string $class
     * @param array $names
     * @param array $enabledCodes
     * @param string $defaultCode
     */
    private function generate($class, array $names, array $enabledCodes, $defaultCode)
    {
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $repository */
        $repository = $this->manager->getRepository($class);

        $enabledCodes = array_map(function($code) {
            return strtoupper($code);
        }, $enabledCodes);

        foreach ($names as $code => $name) {
            $result = 'already exists';
            if (null === $repository->findOneBy(['code' => $code])) {
                /** @var CountryInterface|CurrencyInterface $entity */
                $entity = $repository->createNew();
                $entity
                    ->setName($name)
                    ->setCode($code)
                    ->setEnabled(in_array($code, $enabledCodes))
                    ->setDefault($defaultCode === $code);

                $this->manager->persist($entity);

                $result = 'done';
            }
            call_user_func($this->log, $name, $result);
        }

        $this->manager->flush();
    }
}
