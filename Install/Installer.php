<?php

namespace Ekyna\Component\Commerce\Install;

use Doctrine\Common\Persistence\ObjectManager;
use Ekyna\Component\Commerce\Common\Entity\Country;
use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Entity\Tax;
use Ekyna\Component\Commerce\Pricing\Entity\TaxGroup;
use Ekyna\Component\Commerce\Pricing\Entity\TaxRule;
use Ekyna\Component\Commerce\Stock\Entity\Warehouse;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierTemplateRepositoryInterface;
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
     * @var CustomerGroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var SupplierTemplateRepositoryInterface
     */
    private $supplierTemplateRepository;

    /**
     * @var callable
     */
    private $log;


    /**
     * Constructor.
     *
     * @param ObjectManager                       $manager
     * @param CustomerGroupRepositoryInterface    $customerRepository
     * @param CountryRepositoryInterface          $countryRepository
     * @param SupplierTemplateRepositoryInterface $supplierTemplateRepository
     * @param mixed                               $logger
     */
    public function __construct(
        ObjectManager $manager,
        CustomerGroupRepositoryInterface $customerRepository,
        CountryRepositoryInterface $countryRepository,
        SupplierTemplateRepositoryInterface $supplierTemplateRepository,
        $logger = null
    ) {
        $this->manager = $manager;
        $this->customerGroupRepository = $customerRepository;
        $this->countryRepository = $countryRepository;
        $this->supplierTemplateRepository = $supplierTemplateRepository;

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
     * @param string $country  The default country code
     * @param string $currency The default currency code
     *
     * @throws \Exception
     */
    public function install($country = 'US', $currency = 'USD')
    {
        $this->installCountries($country);
        $this->installCurrencies($currency);
        $this->installTaxes($country);
        $this->installTaxGroups($country);
        $this->installTaxRules($country);
        $this->installCustomerGroups();
        $this->installDefaultWarehouse();
    }

    /**
     * Installs the countries.
     *
     * @param string $code The default country's code
     *
     * @throws \Exception
     */
    public function installCountries($code = 'US')
    {
        $countryNames = Intl::getRegionBundle()->getCountryNames();

        if (!isset($countryNames[$code])) {
            throw new InvalidArgumentException("Invalid default country code '$code'.");
        }

        asort($countryNames);

        $this->generate(Country::class, $countryNames, $code);
    }

    /**
     * Installs the currencies.
     *
     * @param string $code The default currency's code
     *
     * @throws \Exception
     */
    public function installCurrencies($code = 'USD')
    {
        $currencyNames = Intl::getCurrencyBundle()->getCurrencyNames();

        if (!isset($currencyNames[$code])) {
            throw new InvalidArgumentException("Invalid default currency code '$code'.");
        }

        asort($currencyNames);

        $this->generate(Currency::class, $currencyNames, $code);
    }

    /**
     * Installs the taxes for the given country codes.
     *
     * @param array $codes The country codes to load the taxes for
     */
    public function installTaxes($codes = ['US'])
    {
        $codes = (array)$codes;

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

            /** @var CountryInterface $country */
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
    public function installTaxGroups($codes = ['US'])
    {
        $codes = (array)$codes;

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
    public function installTaxRules($codes = ['US'])
    {
        $codes = (array)$codes;

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
        $groups = (array)$this->customerGroupRepository->findBy([], [], 1)->getIterator();
        if (!empty($groups)) {
            call_user_func($this->log, 'All', 'skipped');

            return;
        }

        $groups = [
            'Particuliers' => [
                'default'      => true,
                'business'     => false,
                'registration' => true,
            ],
            'Entreprise'   => [
                'default'      => false,
                'business'     => true,
                'registration' => true,
            ],
        ];

        foreach ($groups as $name => $config) {
            $result = 'already exists';
            if (null === $this->customerGroupRepository->findOneBy(['name' => $name])) {
                /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface $customerGroup */
                $customerGroup = $this->customerGroupRepository->createNew();
                $customerGroup
                    ->setName($name)
                    ->setDefault($config['default'])
                    ->setBusiness($config['business'])
                    ->setRegistration($config['registration'])
                    ->translate()
                    ->setTitle($name);

                $this->manager->persist($customerGroup);

                $result = 'done';
            }

            call_user_func($this->log, $name, $result);
        }

        $this->manager->flush();
    }

    /**
     * Creates the default warehouse (if not exists).
     */
    public function installDefaultWarehouse()
    {
        /** @var \Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface $warehouseRepository */
        $warehouseRepository = $this->manager->getRepository(Warehouse::class);

        if ($warehouse = $warehouseRepository->findDefault(false)) {
            call_user_func($this->log, 'Default', 'already exists');

            return;
        }

        $country = $this->countryRepository->findDefault();

        /** @var \Ekyna\Component\Commerce\Stock\Model\WarehouseInterface $warehouse */
        $warehouse = $warehouseRepository->createNew();
        $warehouse
            ->setName('Default warehouse')
            ->setOffice(true)
            ->setDefault(true)
            ->setCompany('My Company')
            ->setStreet('street')
            ->setCity('city')
            ->setPostalCode('12345')
            ->setCountry($country);

        $this->manager->persist($warehouse);
        $this->manager->flush();

        call_user_func($this->log, 'Default', 'done');
    }

    /**
     * Creates the default supplier templates.
     */
    public function installSupplierTemplates()
    {
        if (0 < $this->supplierTemplateRepository->findBy([], [], 1)->count()) {
            call_user_func($this->log, 'All', 'skipped');

            return;
        }

        $data = Yaml::parse(file_get_contents(__DIR__ . '/data/supplier_templates.yml'));

        foreach ($data as $datum) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface $template */
            $template = $this->supplierTemplateRepository->createNew();
            $template->setTitle($datum['title']);

            foreach ($datum['translations'] as $locale => $trans) {
                $template
                    ->translate($locale, true)
                    ->setSubject($trans['subject'])
                    ->setMessage($trans['message']);
            }

            $this->manager->persist($template);

            call_user_func($this->log, $datum['title'], 'done');
        }

        $this->manager->flush();
    }

    /**
     * Generates the entities.
     *
     * @param string $class
     * @param array  $names
     * @param string $defaultCode
     */
    private function generate($class, array $names, $defaultCode)
    {
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $repository */
        $repository = $this->manager->getRepository($class);

        foreach ($names as $code => $name) {
            $result = 'already exists';
            if (null === $repository->findOneBy(['code' => $code])) {
                /** @var CountryInterface|CurrencyInterface $entity */
                $entity = $repository->createNew();
                $entity
                    ->setName($name)
                    ->setCode($code)
                    ->setEnabled($defaultCode === $code);

                $this->manager->persist($entity);

                $result = 'done';
            }
            call_user_func($this->log, $name, $result);
        }

        $this->manager->flush();
    }
}
