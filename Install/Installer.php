<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Install;

use Closure;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Entity\Country;
use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Pricing\Entity\TaxRuleMention;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierTemplateRepositoryInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Yaml\Yaml;

use function call_user_func;

/**
 * Class Installer
 * @package Ekyna\Component\Commerce\Install
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Installer
{
    private RepositoryFactoryInterface $repositoryFactory;
    private FactoryFactoryInterface    $factoryFactory;
    private ManagerFactoryInterface    $managerFactory;
    private Closure                    $logger;


    public function __construct(
        RepositoryFactoryInterface $repositoryFactory,
        FactoryFactoryInterface $factoryFactory,
        ManagerFactoryInterface $managerFactory,
        callable $logger
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->factoryFactory = $factoryFactory;
        $this->managerFactory = $managerFactory;
        $this->logger = $logger;
    }

    /**
     * Installs the given countries and currencies by codes.
     *
     * @param string $country  The default country code
     * @param string $currency The default currency code
     *
     * @throws \Exception
     */
    public function install(string $country = 'US', string $currency = 'USD'): void
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
        $countryNames = Countries::getNames();

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
        $currencyNames = Currencies::getNames();

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
        $manager = $this->managerFactory->getManager(TaxInterface::class);
        /** @var TaxRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(TaxInterface::class);
        $factory = $this->factoryFactory->getFactory(TaxInterface::class);

        $path = __DIR__ . '/data/taxes.yaml';
        if (!(file_exists($path) && is_readable($path))) {
            throw new Exception\RuntimeException("File $path does not exist.");
        }

        $data = Yaml::parse(file_get_contents($path));
        if (!is_array($data) || empty($data)) {
            throw new Exception\RuntimeException("File $path is invalid or empty.");
        }

        foreach ($data as $code => $datum) {
            if (null === $tax = $repository->findOneByCode($code)) {
                /** @var TaxInterface $tax */
                $tax = $factory->create();
                $tax->setCode($code);
            }

            if ($this->updateTax($tax, $datum)) {
                $manager->persist($tax);
                $result = $tax->getId() ? 'updated' : 'created';
            } else {
                $result = 'skipped';
            }

            $this->logger->call($this, $datum['name'], $result);
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

        $rate = new Decimal($data['rate']);
        if (!$tax->getRate()->equals($rate)) {
            $tax->setRate($rate);
            $changed = true;
        }

        /** @var CountryRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(CountryInterface::class);
        $country = $repository->findOneByCode($data['country']);
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
        $manager = $this->managerFactory->getManager(TaxGroupInterface::class);
        /** @var TaxGroupRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(TaxGroupInterface::class);
        $factory = $this->factoryFactory->getFactory(TaxGroupInterface::class);

        $path = __DIR__ . '/data/tax_groups.yaml';
        if (!(file_exists($path) && is_readable($path))) {
            throw new Exception\RuntimeException("File $path does not exist.");
        }

        $data = Yaml::parse(file_get_contents($path));
        if (!is_array($data) || empty($data)) {
            throw new Exception\RuntimeException("File $path is invalid or empty.");
        }

        foreach ($data as $code => $datum) {
            if (null === $taxGroup = $repository->findOneByCode($code)) {
                /** @var TaxGroupInterface $taxGroup */
                $taxGroup = $factory->create();
                $taxGroup->setCode($code);
            }

            if ($this->updateTaxGroup($taxGroup, $datum)) {
                $manager->persist($taxGroup);
                $result = $taxGroup->getId() ? 'updated' : 'created';
            } else {
                $result = 'skipped';
            }

            $this->logger->call($this, $datum['name'], $result);
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

        /** @var TaxGroupRepositoryInterface $taxGroupRepository */
        $taxGroupRepository = $this->repositoryFactory->getRepository(TaxGroupInterface::class);
        /** @var TaxRepositoryInterface $taxRepository */
        $taxRepository = $this->repositoryFactory->getRepository(TaxInterface::class);

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
                throw new Exception\RuntimeException('Failed to fetch all taxes.');
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
        $manager = $this->managerFactory->getManager(TaxRuleInterface::class);
        /** @var TaxRuleRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(TaxRuleInterface::class);
        $factory = $this->factoryFactory->getFactory(TaxRuleInterface::class);

        $path = __DIR__ . '/data/tax_rules.yaml';
        if (!(file_exists($path) && is_readable($path))) {
            throw new Exception\RuntimeException("File $path does not exist.");
        }

        $data = Yaml::parse(file_get_contents($path));
        if (!is_array($data) || empty($data)) {
            throw new Exception\RuntimeException("File $path is invalid or empty.");
        }

        foreach ($data as $code => $datum) {
            if (null === $taxRule = $repository->findOneByCode($code)) {
                /** @var TaxRuleInterface $taxRule */
                $taxRule = $factory->create();
                $taxRule->setCode($code);
            }

            if ($this->updateTaxRule($taxRule, $datum)) {
                $manager->persist($taxRule);
                $result = $taxRule->getId() ? 'updated' : 'created';
            } else {
                $result = 'skipped';
            }

            $this->logger->call($this, $datum['name'], $result);
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

        /** @var CountryRepositoryInterface $repository */
        $countryRepository = $this->repositoryFactory->getRepository(CountryInterface::class);

        $sources = [];
        if (!empty($data['sources'])) {
            $sources = $countryRepository->findBy(['code' => $data['sources']]);
            if (count($sources) !== count($data['sources'])) {
                throw new Exception\RuntimeException('Failed to fetch all source countries.');
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
                throw new Exception\RuntimeException('Failed to fetch all target countries.');
            }
        }
        if (count($targets) !== count(array_intersect($targets, $rule->getTargets()->toArray()))) {
            $rule->setTargets($targets);
            $changed = true;
        }

        /** @var TaxRepositoryInterface $repository */
        $taxRepository = $this->repositoryFactory->getRepository(TaxInterface::class);

        $taxes = [];
        if (!empty($data['taxes'])) {
            $taxes = $taxRepository->findBy(['code' => $data['taxes']]);
            if (count($taxes) !== count($data['taxes'])) {
                throw new Exception\RuntimeException('Failed to fetch all taxes.');
            }
        }
        if (count($taxes) !== count(array_intersect($taxes, $rule->getTaxes()->toArray()))) {
            $rule->setTaxes($taxes);
            $changed = true;
        }

        if (!empty($data['mentions']) && $rule->getMentions()->isEmpty()) {
            /** @var TaxRuleMention $mention */
            $mention = $this->factoryFactory->getFactory(TaxRuleMention::class)->create();
            $mention
                ->addDocumentType(DocumentTypes::TYPE_INVOICE)
                ->addDocumentType(DocumentTypes::TYPE_PROFORMA);

            foreach ($data['mentions'] as $locale => $content) {
                $mention->translate($locale, true)->setContent($content);
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
    public function installCustomerGroups(): void
    {
        $manager = $this->managerFactory->getManager(CustomerGroupInterface::class);
        /** @var CustomerGroupRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(CustomerGroupInterface::class);
        $factory = $this->factoryFactory->getFactory(CustomerGroupInterface::class);

        $groups = $repository->findBy([], [], 1);
        if (0 < count($groups)) {
            $this->logger->call($this, 'All', 'skipped');

            return;
        }

        // TODO codes
        $groups = [
            'Particuliers' => [
                'default'      => true,
                'business'     => false,
                'registration' => true,
            ],
            'Entreprises'   => [
                'default'      => false,
                'business'     => true,
                'registration' => true,
            ],
        ];

        foreach ($groups as $name => $config) {
            $result = 'already exists';
            if (null === $repository->findOneBy(['name' => $name])) {
                /** @var CustomerGroupInterface $customerGroup */
                $customerGroup = $factory->create();
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

            $this->logger->call($this, $name, $result);
        }

        $manager->flush();
    }

    /**
     * Creates the default warehouse (if not exists).
     */
    public function installDefaultWarehouse(): void
    {
        $manager = $this->managerFactory->getManager(WarehouseInterface::class);
        /** @var WarehouseRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(WarehouseInterface::class);
        $factory = $this->factoryFactory->getFactory(WarehouseInterface::class);

        if ($repository->findDefault(false)) {
            $this->logger->call($this, 'Default', 'already exists');

            return;
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        /** @var CountryInterface $country */
        $country = $this->repositoryFactory->getRepository(CountryInterface::class)->findDefault();

        /** @var WarehouseInterface $warehouse */
        $warehouse = $factory->create();
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

        $this->logger->call($this, 'Default', 'done');
    }

    /**
     * Creates the default supplier templates.
     */
    public function installSupplierTemplates(): void
    {
        $manager = $this->managerFactory->getManager(SupplierTemplateInterface::class);
        /** @var SupplierTemplateRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(SupplierTemplateInterface::class);
        $factory = $this->factoryFactory->getFactory(SupplierTemplateInterface::class);

        if (0 < count($repository->findBy([], [], 1))) {
            $this->logger->call($this, 'All', 'skipped');

            return;
        }

        $data = Yaml::parse(file_get_contents(__DIR__ . '/data/supplier_templates.yaml'));

        foreach ($data as $datum) {
            /** @var SupplierTemplateInterface $template */
            $template = $factory->create();
            $template->setTitle($datum['title']);

            foreach ($datum['translations'] as $locale => $trans) {
                $template
                    ->translate($locale, true)
                    ->setSubject($trans['subject'])
                    ->setMessage($trans['message']);
            }

            $manager->persist($template);

            call_user_func($this->logger, $datum['title'], 'done');
        }

        $manager->flush();
    }

    /**
     * Generates the entities.
     */
    private function generate(string $class, array $names, string $defaultCode): void
    {
        $manager = $this->managerFactory->getManager($class);
        $repository = $this->repositoryFactory->getRepository($class);
        $factory = $this->factoryFactory->getFactory($class);

        foreach ($names as $code => $name) {
            if ($repository->findOneBy(['code' => $code])) {
                $this->logger->call($this, $name, 'already exists');

                continue;
            }

            /** @var CountryInterface|CurrencyInterface $entity */
            $entity = $factory->create();
            $entity
                ->setName($name)
                ->setCode($code)
                ->setEnabled($defaultCode === $code);

            $manager->persist($entity);

            $this->logger->call($this, $name, 'done');
        }

        $manager->flush();
    }
}
