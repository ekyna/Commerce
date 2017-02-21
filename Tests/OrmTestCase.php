<?php

namespace Ekyna\Component\Commerce\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CustomerEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderEventSubscriber;
//use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\ProductEventSubscriber;
use Ekyna\Component\Commerce\Common\Builder\AddressBuilder;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilder;
use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculator;
use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Factory\SaleFactory;
use Ekyna\Component\Commerce\Common\Generator\DefaultKeyGenerator;
use Ekyna\Component\Commerce\Common\Generator\DefaultNumberGenerator;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdater;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroup;
use Ekyna\Component\Commerce\Order\Resolver\OrderStateResolver;
use Ekyna\Component\Resource\ResourceComponent;
use Symfony\Component\Yaml\Yaml;

/**
 * Class OrmTestCase
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class OrmTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    /*protected static $_aliases = [
        'cart'               => 'Ekyna\Component\Commerce\Cart\Entity\Cart',
        'cartAddress'        => 'Ekyna\Component\Commerce\Cart\Entity\CartAddress',
        'cartAdjustment'     => 'Ekyna\Component\Commerce\Cart\Entity\CartAdjustment',
        'cartItem'           => 'Ekyna\Component\Commerce\Cart\Entity\CartItem',
        'cartItemAdjustment' => 'Ekyna\Component\Commerce\Cart\Entity\CartItemAdjustment',

        'country'  => 'Ekyna\Component\Commerce\Common\Entity\Country',
        'currency' => 'Ekyna\Component\Commerce\Common\Entity\Currency',

        'customer'        => 'Ekyna\Component\Commerce\Customer\Entity\Customer',
        'customerAddress' => 'Ekyna\Component\Commerce\Customer\Entity\CustomerAddress',
        'customerGroup'   => 'Ekyna\Component\Commerce\Customer\Entity\CustomerGroup',

        'order'               => 'Ekyna\Component\Commerce\Order\Entity\Order',
        'orderAddress'        => 'Ekyna\Component\Commerce\Order\Entity\OrderAddress',
        'orderAdjustment'     => 'Ekyna\Component\Commerce\Order\Entity\OrderAdjustment',
        'orderItem'           => 'Ekyna\Component\Commerce\Order\Entity\OrderItem',
        'orderItemAdjustment' => 'Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment',

        // TODO 'payment'   => 'Ekyna\Component\Commerce\Payment\Entity\Payment',
        'paymentMessage'  => 'Ekyna\Component\Commerce\Payment\Entity\PaymentMessage',
        'paymentMethod'   => 'Ekyna\Component\Commerce\Payment\Entity\PaymentMethod',

        'quote'               => 'Ekyna\Component\Commerce\Quote\Entity\Quote',
        'quoteAddress'        => 'Ekyna\Component\Commerce\Quote\Entity\QuoteAddress',
        'quoteAdjustment'     => 'Ekyna\Component\Commerce\Quote\Entity\QuoteAdjustment',
        'quoteItem'           => 'Ekyna\Component\Commerce\Quote\Entity\QuoteItem',
        'quoteItemAdjustment' => 'Ekyna\Component\Commerce\Quote\Entity\QuoteItemAdjustment',

        // TODO 'stockUnit' => 'Ekyna\Component\Commerce\Stock\Entity\StockUnit',

        // TODO 'shipment'   => 'Ekyna\Component\Commerce\Shipment\Entity\Shipment',
        'shipmentMessage' => 'Ekyna\Component\Commerce\Shipment\Entity\ShipmentMessage',
        'shipmentMethod'  => 'Ekyna\Component\Commerce\Shipment\Entity\ShipmentMethod',
        'shipmentPrice'   => 'Ekyna\Component\Commerce\Shipment\Entity\ShipmentPrice',
        'shipmentZone'    => 'Ekyna\Component\Commerce\Shipment\Entity\ShipmentZone',

        'supplier'             => 'Ekyna\Component\Commerce\Supplier\Entity\Supplier',
        'supplierAddress'      => 'Ekyna\Component\Commerce\Supplier\Entity\SupplierAddress',
        'supplierDelivery'     => 'Ekyna\Component\Commerce\Supplier\Entity\SupplierDelivery',
        'supplierDeliveryItem' => 'Ekyna\Component\Commerce\Supplier\Entity\SupplierDeliveryItem',
        'supplierOrder'        => 'Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder',
        'supplierOrderItem'    => 'Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem',
        'supplierProduct'      => 'Ekyna\Component\Commerce\Supplier\Entity\SupplierProduct',

        'tax'      => 'Ekyna\Component\Commerce\Pricing\Entity\Tax',
        'taxGroup' => 'Ekyna\Component\Commerce\Pricing\Entity\TaxGroup',
        'taxRule'  => 'Ekyna\Component\Commerce\Pricing\Entity\TaxRule',
    ];*/

    /**
     * @var EntityManager
     */
    protected static $em;

    /**
     * @var ResourceComponent
     */
    protected static $rc;


    public static function setUpBeforeClass()
    {
        if (false == class_exists('Doctrine\ORM\Version', true)) {
            throw new \PHPUnit_Framework_SkippedTestError('Doctrine ORM lib not installed. Have you run composer with --dev option?');
        }
        if (false == extension_loaded('pdo_sqlite')) {
            throw new \PHPUnit_Framework_SkippedTestError('The pdo_sqlite extension is not loaded. It is required to run doctrine tests.');
        }

        static::setUpEntityManager();
        static::setUpResourceComponent();

        static::setUpDatabase();
        static::loadFixtures();
    }

    /*public static function tearDownAfterClass()
    {
        static::dropDatabase();
    }*/

    protected static function setUpEntityManager()
    {
        // Drive
        $rootDir = realpath(__DIR__ . '/..');
        if (false === $rootDir || false === is_file($rootDir . '/Commerce.php')) {
            throw new \RuntimeException('Cannot guess Commerce root dir.');
        }
        $driver = new SimplifiedXmlDriver([
            $rootDir . '/Bridge/Doctrine/ORM/Resources/mapping' => 'Ekyna\Component\Commerce',
        ]);

        // Custom mapping types
        Type::addType('phone_number', 'Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType');

        // Configuration
        $config = new Configuration();
        $config->setSQLLogger(null);
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(sys_get_temp_dir().'/doctrine-proxies');
        $config->setProxyNamespace('Proxies');
        $config->setMetadataDriverImpl($driver);
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        // Event manager
        $evm = new EventManager();

        // Connection
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            //'path'   => ':memory:',
            'memory' => true,
        ], $config, $evm);

        static::$em = EntityManager::create($connection, $config, $evm);
    }

    protected static function setUpResourceComponent()
    {
        // Resource component
        $rc = new ResourceComponent(static::$em);
        $definitions = Yaml::parse(file_get_contents(__DIR__ . '/../Bridge/Symfony/Resources/resources.yml'));
        $interfaceMap = DoctrineBundleMapping::getDefaultImplementations();
        $rc->configureResources($definitions, $interfaceMap);

        $dispatcher = $rc->getEventDispatcher();
        $persistenceHelper = $rc->getPersistenceHelper();

        // Customer listener
        $dispatcher->addSubscriber(new CustomerEventSubscriber($persistenceHelper));

        // TODO $dispatcher->addSubscriber(new ProductEventSubscriber());

        /** @noinspection PhpParamsInspection */
        $saleFactory = new SaleFactory(
            static::$em->getRepository(CustomerGroup::class),
            static::$em->getRepository(Currency::class)
        );

        // Sale updater
        $saleUpdater = new SaleUpdater(
            new AddressBuilder($saleFactory, $persistenceHelper),
            new AdjustmentBuilder($saleFactory, $persistenceHelper)
        );

        // Order listener
        $orderListener = new OrderEventSubscriber(
            new DefaultNumberGenerator(),
            new AmountsCalculator(),
            new OrderStateResolver()
        );
        $orderListener->setPersistenceHelper($persistenceHelper);
        $orderListener->setNumberGenerator(new DefaultNumberGenerator());
        $orderListener->setKeyGenerator(new DefaultKeyGenerator());
        $orderListener->setSaleUpdater();
        $orderListener->setStateResolver(new OrderStateResolver());
        $dispatcher->addSubscriber($orderListener);

        static::$rc = $rc;
    }

    protected static function setUpDatabase()
    {
        $classes = static::$em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool(static::$em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }

    /*protected static function dropDatabase()
    {
        $classes = static::$em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool(static::$em);
        $schemaTool->dropSchema($classes);
    }*/

    protected static function loadFixtures()
    {
        $loader = new Loader();
        $loader->loadFromDirectory(__DIR__ . '/../Bridge/Doctrine/Fixtures');
        $purger = new ORMPurger();
        $executor = new ORMExecutor(static::$em, $purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * Returns the entity manager.
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return static::$em;
    }

    /**
     * Returns the resource fully qualified class.
     *
     * @param string $resource
     *
     * @return string
     */
    protected function getResourceClass($resource)
    {
        return static::$rc
            ->getConfigurationRegistry()
            ->get('ekyna_commerce.' . $resource)
            ->getResourceClass();
    }

    /**
     * Returns the resource repository.
     *
     * @param string $resource
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getResourceRepository($resource)
    {
        return static::$em->getRepository($this->getResourceClass($resource));
    }

    /**
     * Finds the resource by id.
     *
     * @param string $class
     * @param int    $id
     *
     * @return null|object
     */
    protected function find($class, $id)
    {
        return $this->getResourceRepository($class)->find($id);
    }
}
