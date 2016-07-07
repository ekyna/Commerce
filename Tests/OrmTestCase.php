<?php

namespace Ekyna\Component\Commerce\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Bridge\Doctrine\Listener\LoadMetadataSubscriber;

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
    protected static $_aliases = [
        'country'             => 'Ekyna\Component\Commerce\Address\Entity\Country',

        'currency'            => 'Ekyna\Component\Commerce\Pricing\Entity\Currency',
        'priceList'           => 'Ekyna\Component\Commerce\Pricing\Entity\PriceList',
        'tax'                 => 'Ekyna\Component\Commerce\Pricing\Entity\Tax',
        'taxGroup'            => 'Ekyna\Component\Commerce\Pricing\Entity\TaxGroup',
        'taxRule'             => 'Ekyna\Component\Commerce\Pricing\Entity\TaxRule',

        'customer'            => 'Ekyna\Component\Commerce\Customer\Entity\Customer',
        'customerAddress'     => 'Ekyna\Component\Commerce\Customer\Entity\CustomerAddress',
        'customerGroup'       => 'Ekyna\Component\Commerce\Customer\Entity\CustomerGroup',

        'subject'             => 'Ekyna\Component\Commerce\Subject\Entity\Subject',
        'offer'               => 'Ekyna\Component\Commerce\Subject\Entity\Offer',

        'order'               => 'Ekyna\Component\Commerce\Order\Entity\Order',
        'orderAddress'        => 'Ekyna\Component\Commerce\Order\Entity\OrderAddress',
        'orderAdjustment'     => 'Ekyna\Component\Commerce\Order\Entity\OrderAdjustment',
        'orderItem'           => 'Ekyna\Component\Commerce\Order\Entity\OrderItem',
        'orderItemAdjustment' => 'Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment',
    ];

    /**
     * @var EntityManager
     */
    protected static $em;

    public static function setUpBeforeClass()
    {
        if (false == class_exists('Doctrine\ORM\Version', true)) {
            throw new \PHPUnit_Framework_SkippedTestError('Doctrine ORM lib not installed. Have you run composer with --dev option?');
        }
        if (false == extension_loaded('pdo_sqlite')) {
            throw new \PHPUnit_Framework_SkippedTestError('The pdo_sqlite extension is not loaded. It is required to run doctrine tests.');
        }
        static::setUpEntityManager();
        static::setUpDatabase();
        static::loadFixtures();
    }

    /*protected function setUp()
    {

    }*/

    protected static function setUpEntityManager()
    {
        $config = new Configuration();
        $config->setSQLLogger(null);
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');
        $config->setMetadataDriverImpl(static::getMetadataDriverImpl());
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        $dbPath = __DIR__ . '/../db.sqlite';
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        $connection = [
            'driver' => 'pdo_sqlite',
            'path'   => $dbPath,
            //'path' => ':memory:'
        ];

        // Event listeners
        $interfaces = DoctrineBundleMapping::getDefaultImplementations();
        $evm = new EventManager();

        // Resolve entity target subscriber
        $rtel = new ResolveTargetEntityListener();
        foreach ($interfaces as $model => $implementation) {
            $rtel->addResolveTargetEntity($model, $implementation, []);
        }
        $evm->addEventSubscriber($rtel);

        // Load metadata subscriber
        $lm = new LoadMetadataSubscriber([], $interfaces);
        $evm->addEventSubscriber($lm);

        static::$em = EntityManager::create($connection, $config, $evm);
    }

    /**
     * @return MappingDriver
     */
    protected static function getMetadataDriverImpl()
    {
        $rootDir = realpath(__DIR__ . '/..');
        if (false === $rootDir || false === is_file($rootDir . '/Commerce.php')) {
            throw new \RuntimeException('Cannot guess Commerce root dir.');
        }
        $driver = new SimplifiedXmlDriver([
            $rootDir . '/Bridge/Doctrine/ORM/Resources/mapping' => 'Ekyna\Component\Commerce',
        ]);

        return $driver;
    }

    protected static function setUpDatabase()
    {
        $classes = static::$em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool(static::$em);
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }

    protected static function loadFixtures()
    {
        $loader = new Loader();
        $loader->loadFromDirectory(__DIR__ . '/../Bridge/Doctrine/Fixtures');
        $purger = new ORMPurger();
        $executor = new ORMExecutor(static::$em, $purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * Returns the FQCN by name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getClass($name)
    {
        if (!array_key_exists($name, static::$_aliases)) {
            throw new \InvalidArgumentException("Undefined class '{$name}'.");
        }

        return static::$_aliases[$name];
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return static::$em;
    }

    /**
     * Returns the repository by class.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($class)
    {
        if (!class_exists($class)) {
            $class = $this->getClass($class);
        }

        return static::$em->getRepository($class);
    }

    /**
     * Finds the entity by id.
     *
     * @param string $class
     * @param int    $id
     *
     * @return null|object
     */
    protected function find($class, $id)
    {
        return $this->getRepository($class)->find($id);
    }
}
