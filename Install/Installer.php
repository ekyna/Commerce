<?php

namespace Ekyna\Component\Commerce\Install;

use Doctrine\Common\Persistence\ObjectManager;

use Ekyna\Component\Commerce\Common\Entity\Country;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroup;
use Ekyna\Component\Commerce\Pricing\Entity\Tax;
use Ekyna\Component\Commerce\Pricing\Entity\TaxGroup;
use Ekyna\Component\Commerce\Pricing\Entity\TaxRule;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Yaml\Yaml;

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
            $this->log = function ($name, $result) use ($logger) {
                /** @var \Symfony\Component\Console\Output\OutputInterface $logger */
                $logger->writeln(sprintf(
                    '- <comment>%s</comment> %s %s.',
                    $name,
                    str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT),
                    $result
                ));
            };
        } else {
            $this->log = function ($name, $result) {
            };
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
    public function install($countries = ['FR'], $currencies = ['EUR'])
    {
        $this->installCountries($countries);
        $this->installCurrencies($currencies);
        $this->installTaxes($countries);
        $this->installTaxGroups($countries);
        $this->installTaxRules($countries);
        $this->installCustomerGroups();
    }

    /**
     * Installs the given countries by codes.
     *
     * @param array $codes The enabled country codes (the first will be set as default)
     *
     * @throws \Exception
     */
    public function installCountries($codes = ['FR'])
    {
        if (empty($codes)) {
            throw new \Exception("Expected non empty array of enabled country codes.");
        }

        $countryNames = Intl::getRegionBundle()->getCountryNames();
        asort($countryNames);

        $this->generate(Country::class, $countryNames, $codes, $codes[0]);
    }

    /**
     * Installs the given currencies by codes.
     *
     * @param array $codes The enabled currency codes (the first will be set as default)
     *
     * @throws \Exception
     */
    public function installCurrencies($codes = ['EUR'])
    {
        if (empty($codes)) {
            throw new \Exception("Expected non empty array of currency codes.");
        }

        $currencyNames = Intl::getCurrencyBundle()->getCurrencyNames();
        asort($currencyNames);

        $this->generate(Currency::class, $currencyNames, $codes, $codes[0]);
    }

    /**
     * Installs the taxes for the given country codes.
     *
     * @param array $codes The country codes to load the taxes for
     */
    public function installTaxes($codes = ['FR'])
    {
        if (empty($codes)) {
            return;
        }

        $taxRepository = $this->manager->getRepository(Tax::class);

        foreach ($codes as $code) {
            $path = __DIR__ . '/data/' . $code . '_taxes.yml';
            if (!(file_exists($path) && is_readable($path))) {
                call_user_func($this->log, 'Taxes data', 'not found');
                continue;
            }

            $data = Yaml::parse(file_get_contents($path));
            if (!is_array($data) || empty($data)) {
                continue;
            }

            $country = $this
                ->manager
                ->getRepository(Country::class)
                ->findOneBy(['code' => $code]);
            if (null === $country) {
                continue;
            }

            foreach ($data as $datum) {
                $name = $datum['name'];
                $result = 'already exists';

                if (null === $taxRepository->findOneBy(['name' => $name])) {
                    $tax = new Tax();
                    $tax
                        ->setName($name)
                        ->setRate($datum['rate'])
                        ->setCountry($country);

                    $this->manager->persist($tax);

                    $result = 'done';
                }

                call_user_func($this->log, $name, $result);
            }
        }

        $this->manager->flush();
    }

    /**
     * Installs the default tax groups.
     *
     * @param array $codes The country codes to load the taxes for
     */
    public function installTaxGroups($codes = ['FR'])
    {
        if (empty($codes)) {
            return;
        }

        $taxGroupRepository = $this->manager->getRepository(TaxGroup::class);
        $taxRepository = $this->manager->getRepository(Tax::class);

        foreach ($codes as $code) {
            $path = __DIR__ . '/data/' . $code . '_tax_groups.yml';
            if (!(file_exists($path) && is_readable($path))) {
                call_user_func($this->log, 'Tax groups data', 'not found');
                continue;
            }

            $data = Yaml::parse(file_get_contents($path));
            if (!is_array($data) || empty($data)) {
                continue;
            }

            foreach ($data as $datum) {
                $name = $datum['name'];
                $result = 'already exists';

                if ($datum['default']) {
                    $taxGroup = $this
                        ->manager
                        ->getRepository(TaxGroup::class)
                        ->findOneBy(['default' => true]);
                    if (null !== $taxGroup) {
                        call_user_func($this->log, $name, 'skipped');
                        continue;
                    }
                }

                if (null === $taxGroupRepository->findOneBy(['name' => $name])) {
                    $taxGroup = new TaxGroup();
                    $taxGroup
                        ->setName($name)
                        ->setDefault($datum['default']);

                    if (!empty($taxNames = $datum['taxes'])) {
                        $taxGroup->setTaxes($taxRepository->findBy(['name' => $taxNames]));
                    }

                    $this->manager->persist($taxGroup);

                    $result = 'done';
                }

                call_user_func($this->log, $name, $result);
            }
        }

        $this->manager->flush();
    }

    /**
     * Installs the tax rules for the given country codes.
     *
     * @param array $codes The country codes to load the tax rules for
     */
    public function installTaxRules($codes = ['FR'])
    {
        if (empty($codes)) {
            return;
        }

        $countryRepository = $this->manager->getRepository(Country::class);
        $taxRepository = $this->manager->getRepository(Tax::class);
        $taxRuleRepository = $this->manager->getRepository(TaxRule::class);

        foreach ($codes as $code) {
            $path = __DIR__ . '/data/' . $code . '_tax_rules.yml';
            if (!(file_exists($path) && is_readable($path))) {
                call_user_func($this->log, 'Tax rules data', 'not found');
                continue;
            }

            $data = Yaml::parse(file_get_contents($path));
            if (!is_array($data) || empty($data)) {
                continue;
            }

            foreach ($data as $datum) {
                $name = $datum['name'];
                $result = 'already exists';

                if (null === $taxRuleRepository->findOneBy(['name' => $name])) {
                    $taxRule = new TaxRule();
                    $taxRule
                        ->setName($name)
                        ->setPriority($datum['priority'])
                        ->setCustomer($datum['customer'])
                        ->setBusiness($datum['business'])
                        ->setNotices($datum['notices']);

                    if (!empty($countryCodes = $datum['countries'])) {
                        $taxRule->setCountries($countryRepository->findBy(['code' => $countryCodes]));
                    }

                    if (!empty($taxNames = $datum['taxes'])) {
                        $taxRule->setTaxes($taxRepository->findBy(['name' => $taxNames]));
                    }

                    $this->manager->persist($taxRule);

                    $result = 'done';
                }

                call_user_func($this->log, $name, $result);
            }
        }

        $this->manager->flush();
    }

    /**
     * Installs the default customer groups.
     */
    public function installCustomerGroups()
    {
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $repository */
        $repository = $this->manager->getRepository(CustomerGroup::class);

        $name = 'Default customer group';

        $result = 'already exists';
        if (null === $repository->findOneBy(['default' => true])) {
            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface $customerGroup */
            $customerGroup = $repository->createNew();
            $customerGroup
                ->setName($name)
                ->setDefault(true);

            $this->manager->persist($customerGroup);
            $this->manager->flush();

            $result = 'done';
        }

        call_user_func($this->log, $name, $result);
    }

    /**
     * Generates the entities.
     *
     * @param string $class
     * @param array  $names
     * @param array  $enabledCodes
     * @param string $defaultCode
     */
    private function generate($class, array $names, array $enabledCodes, $defaultCode)
    {
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $repository */
        $repository = $this->manager->getRepository($class);

        $enabledCodes = array_map(function ($code) {
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
