<?php

namespace Ekyna\Component\Commerce\Install;

use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Common\Entity\Country;
use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroup;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Pricing\Entity\Tax;
use Ekyna\Component\Commerce\Pricing\Entity\TaxGroup;
use Ekyna\Component\Commerce\Pricing\Entity\TaxRule;
use Ekyna\Component\Commerce\Pricing\Entity\TaxRuleMention;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Commerce\Stock\Entity\Warehouse;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierTemplate;
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
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var callable
     */
    private $log;


    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     * @param mixed           $logger
     */
    public function __construct(
        ManagerRegistry $registry,
        $logger = null
    ) {
        $this->registry = $registry;

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
        $this->installTaxes();
        $this->installTaxGroups();
        $this->installTaxRules();
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
    public function installCountries(string $code = 'US'): void
    {
        $countryNames = Intl::getRegionBundle()->getCountryNames();

        if (!isset($countryNames[$code])) {
            throw new Exception\InvalidArgumentException("Invalid default country code '$code'.");
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
    public function installCurrencies(string $code = 'USD'): void
    {
        $currencyNames = Intl::getCurrencyBundle()->getCurrencyNames();

        if (!isset($currencyNames[$code])) {
            throw new Exception\InvalidArgumentException("Invalid default currency code '$code'.");
        }

        asort($currencyNames);

        $this->generate(Currency::class, $currencyNames, $code);
    }

    /**
     * Installs the taxes for the given country codes.
     */
    public function installTaxes(): void
    {
        $manager    = $this->registry->getManagerForClass(Tax::class);
        $repository = $this->registry->getRepository(Tax::class);

        $path = __DIR__ . '/data/taxes.yml';
        if (!(file_exists($path) && is_readable($path))) {
            throw new Exception\RuntimeException("File $path does not exist.");
        }

        $data = Yaml::parse(file_get_contents($path));
        if (!is_array($data) || empty($data)) {
            throw new Exception\RuntimeException("File $path is invalid or empty.");
        }

        foreach ($data as $code => $datum) {
            if (null === $tax = $repository->findOneByCode($code)) {
                $tax = new Tax();
                $tax->setCode($code);
            }

            if ($this->updateTax($tax, $datum)) {
                $manager->persist($tax);
                $result = $tax->getId() ? 'updated' : 'created';
            } else {
                $result = 'skipped';
            }

            call_user_func($this->log, $datum['name'], $result);
        }

        $manager->flush();
    }

    /**
     * Updates the given tax.
     *
     * @param TaxInterface $tax
     * @param array        $data
     *
     * @return bool Whether the tax has been changed.
     */
    private function updateTax(TaxInterface $tax, array $data): bool
    {
        $changed = false;

        if ($tax->getName() !== $data['name']) {
            $tax->setName($data['name']);
            $changed = true;
        }

        if (0 !== bccomp($tax->getRate(), $data['rate'], 3)) {
            $tax->setRate($data['rate']);
            $changed = true;
        }

        $country = $this->registry->getRepository(Country::class)->findOneByCode($data['country']);
        if (null === $country) {
            throw new Exception\RuntimeException("Country {$data['country']} not found.");
        }
        if ($tax->getCountry() !== $country) {
            $tax->setCountry($country);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Installs the default tax groups.
     */
    public function installTaxGroups(): void
    {
        $manager    = $this->registry->getManagerForClass(TaxGroup::class);
        $repository = $this->registry->getRepository(TaxGroup::class);

        $path = __DIR__ . '/data/tax_groups.yml';
        if (!(file_exists($path) && is_readable($path))) {
            throw new Exception\RuntimeException("File $path does not exist.");
        }

        $data = Yaml::parse(file_get_contents($path));
        if (!is_array($data) || empty($data)) {
            throw new Exception\RuntimeException("File $path is invalid or empty.");
        }

        foreach ($data as $code => $datum) {
            if (null === $taxGroup = $repository->findOneByCode($code)) {
                $taxGroup = new TaxGroup();
                $taxGroup->setCode($code);
            }

            if ($this->updateTaxGroup($taxGroup, $datum)) {
                $manager->persist($taxGroup);
                $result = $taxGroup->getId() ? 'updated' : 'created';
            } else {
                $result = 'skipped';
            }

            call_user_func($this->log, $datum['name'], $result);
        }

        $manager->flush();
    }

    /**
     * Updates the tax group.
     *
     * @param TaxGroupInterface $group
     * @param array             $data
     *
     * @return bool
     */
    private function updateTaxGroup(TaxGroupInterface $group, array $data): bool
    {
        $changed = false;

        $taxGroupRepository = $this->registry->getRepository(TaxGroup::class);
        $taxRepository      = $this->registry->getRepository(Tax::class);

        if ($data['default'] && !$taxGroupRepository->findDefault(false)) {
            $group->setDefault(true);
            $changed = true;
        }

        if ($group->getName() !== $data['name']) {
            $group->setName($data['name']);
            $changed = true;
        }

        $taxes = [];
        if (!empty($data['taxes'])) {
            $taxes = $taxRepository->findBy(['code' => $data['taxes']]);
            if (count($taxes) !== count($data['taxes'])) {
                throw new Exception\RuntimeException("Failed to fetch all taxes.");
            }
        }
        if (count($taxes) !== count(array_intersect($taxes, $group->getTaxes()->toArray()))) {
            $group->setTaxes($taxes);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Installs the tax rules for the given country codes.
     */
    public function installTaxRules(): void
    {
        $manager    = $this->registry->getManagerForClass(TaxRule::class);
        $repository = $this->registry->getRepository(TaxRule::class);

        $path = __DIR__ . '/data/tax_rules.yml';
        if (!(file_exists($path) && is_readable($path))) {
            throw new Exception\RuntimeException("File $path does not exist.");
        }

        $data = Yaml::parse(file_get_contents($path));
        if (!is_array($data) || empty($data)) {
            throw new Exception\RuntimeException("File $path is invalid or empty.");
        }

        foreach ($data as $code => $datum) {
            if (null === $taxRule = $repository->findOneByCode($code)) {
                $taxRule = new TaxRule();
                $taxRule->setCode($code);
            }

            if ($this->updateTaxRule($taxRule, $datum)) {
                $manager->persist($taxRule);
                $result = $taxRule->getId() ? 'updated' : 'created';
            } else {
                $result = 'skipped';
            }

            call_user_func($this->log, $datum['name'], $result);
        }

        $manager->flush();
    }

    /**
     * Updates the tax rule.
     *
     * @param TaxRuleInterface $rule
     * @param array            $data
     *
     * @return bool
     */
    private function updateTaxRule(TaxRuleInterface $rule, array $data): bool
    {
        $changed = false;

        if ($rule->getName() !== $data['name']) {
            $rule->setName($data['name']);
            $changed = true;
        }

        if ($rule->isCustomer() xor $data['customer']) {
            $rule->setCustomer($data['customer']);
            $changed = true;
        }

        if ($rule->isBusiness() xor $data['business']) {
            $rule->setBusiness($data['business']);
            $changed = true;
        }

        $countryRepository = $this->registry->getRepository(Country::class);

        $sources = [];
        if (!empty($data['sources'])) {
            $sources = $countryRepository->findBy(['code' => $data['sources']]);
            if (count($sources) !== count($data['sources'])) {
                throw new Exception\RuntimeException("Failed to fetch all source countries.");
            }
        }
        if (count($sources) !== count(array_intersect($sources, $rule->getSources()->toArray()))) {
            $rule->setSources($sources);
            $changed = true;
        }

        $targets = [];
        if (!empty($data['targets'])) {
            $targets = $countryRepository->findBy(['code' => $data['targets']]);
            if (count($targets) !== count($data['targets'])) {
                throw new Exception\RuntimeException("Failed to fetch all target countries.");
            }
        }
        if (count($targets) !== count(array_intersect($targets, $rule->getTargets()->toArray()))) {
            $rule->setTargets($targets);
            $changed = true;
        }

        $taxRepository = $this->registry->getRepository(Tax::class);

        $taxes = [];
        if (!empty($data['taxes'])) {
            $taxes = $taxRepository->findBy(['code' => $data['taxes']]);
            if (count($taxes) !== count($data['taxes'])) {
                throw new Exception\RuntimeException("Failed to fetch all taxes.");
            }
        }
        if (count($taxes) !== count(array_intersect($taxes, $rule->getTaxes()->toArray()))) {
            $rule->setTaxes($taxes);
            $changed = true;
        }

        if (!empty($data['mentions']) && $rule->getMentions()->isEmpty()) {
            /** @var TaxRuleMention $mention */
            $mention = $this->registry->getRepository(TaxRuleMention::class)->createNew();
            $mention
                ->addDocumentType(DocumentTypes::TYPE_INVOICE)
                ->addDocumentType(DocumentTypes::TYPE_PROFORMA);

            foreach ($data['mentions'] as $locale => $content) {
                $mention->translate($locale)->setContent($content);
            }

            $rule->addMention($mention);

            $changed = true;
        }

        if ($rule->getPriority() !== $data['priority']) {
            $rule->setPriority($data['priority']);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Installs the default customer groups.
     */
    public function installCustomerGroups()
    {
        $manager    = $this->registry->getManagerForClass(CustomerGroup::class);
        $repository = $this->registry->getRepository(CustomerGroup::class);

        $groups = $repository->findBy([], [], 1);
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
            if (null === $repository->findOneBy(['name' => $name])) {
                /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface $customerGroup */
                $customerGroup = $repository->createNew();
                $customerGroup
                    ->setName($name)
                    ->setDefault($config['default'])
                    ->setBusiness($config['business'])
                    ->setRegistration($config['registration'])
                    ->translate()
                    ->setTitle($name);

                $manager->persist($customerGroup);

                $result = 'done';
            }

            call_user_func($this->log, $name, $result);
        }

        $manager->flush();
    }

    /**
     * Creates the default warehouse (if not exists).
     */
    public function installDefaultWarehouse()
    {
        $manager = $this->registry->getManagerForClass(CustomerGroup::class);

        /** @var \Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface $warehouseRepository */
        $warehouseRepository = $this->registry->getRepository(Warehouse::class);

        if ($warehouse = $warehouseRepository->findDefault(false)) {
            call_user_func($this->log, 'Default', 'already exists');

            return;
        }

        $country = $this->registry->getRepository(Country::class)->findDefault();

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

        $manager->persist($warehouse);
        $manager->flush();

        call_user_func($this->log, 'Default', 'done');
    }

    /**
     * Creates the default supplier templates.
     */
    public function installSupplierTemplates()
    {
        $manager    = $this->registry->getManagerForClass(SupplierTemplate::class);
        $repository = $this->registry->getRepository(SupplierTemplate::class);

        if (0 < count($repository->findBy([], [], 1))) {
            call_user_func($this->log, 'All', 'skipped');

            return;
        }

        $data = Yaml::parse(file_get_contents(__DIR__ . '/data/supplier_templates.yml'));

        foreach ($data as $datum) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface $template */
            $template = $repository->createNew();
            $template->setTitle($datum['title']);

            foreach ($datum['translations'] as $locale => $trans) {
                $template
                    ->translate($locale, true)
                    ->setSubject($trans['subject'])
                    ->setMessage($trans['message']);
            }

            $manager->persist($template);

            call_user_func($this->log, $datum['title'], 'done');
        }

        $manager->flush();
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
        $manager = $this->registry->getManagerForClass($class);
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $repository */
        $repository = $this->registry->getRepository($class);

        foreach ($names as $code => $name) {
            $result = 'already exists';
            if (null === $repository->findOneBy(['code' => $code])) {
                /** @var CountryInterface|CurrencyInterface $entity */
                $entity = $repository->createNew();
                $entity
                    ->setName($name)
                    ->setCode($code)
                    ->setEnabled($defaultCode === $code);

                $manager->persist($entity);

                $result = 'done';
            }
            call_user_func($this->log, $name, $result);
        }

        $manager->flush();
    }
}
