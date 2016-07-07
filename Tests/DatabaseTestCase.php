<?php

namespace Ekyna\Component\Commerce\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

/**
 * Class DatabaseTestCase
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see     https://github.com/doctrine/doctrine2/blob/master/tests/Doctrine/Tests/OrmFunctionalTestCase.php
 */
abstract class DatabaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected static $_conn;

    /**
     * @var EntityManager
     */
    protected static $_em;

    /**
     * @var array
     */
    protected static $_classes = [
        'country' => 'Ekyna\Component\Commerce\Address\Entity\Country',

        'currency'  => 'Ekyna\Component\Commerce\Pricing\Entity\Currency',
        'tax'       => 'Ekyna\Component\Commerce\Pricing\Entity\Tax',
        'taxGroup'  => 'Ekyna\Component\Commerce\Pricing\Entity\TaxGroup',
        'taxRule'   => 'Ekyna\Component\Commerce\Pricing\Entity\TaxRule',
        'priceList' => 'Ekyna\Component\Commerce\Pricing\Entity\PriceList',

        'customerAddress' => 'Ekyna\Component\Commerce\Customer\Entity\CustomerAddress',
        'customerGroup'   => 'Ekyna\Component\Commerce\Customer\Entity\CustomerGroup',
        'customer'        => 'Ekyna\Component\Commerce\Customer\Entity\Customer',

        'subject' => 'Ekyna\Component\Commerce\Subject\Entity\Subject',
        'offer'   => 'Ekyna\Component\Commerce\Subject\Entity\Offer',

        'orderAddress'        => 'Ekyna\Component\Commerce\Order\Entity\OrderAddress',
        'orderItemAdjustment' => 'Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment',
        'orderItem'           => 'Ekyna\Component\Commerce\Order\Entity\OrderItem',
        'orderAdjustment'     => 'Ekyna\Component\Commerce\Order\Entity\OrderAdjustment',
        'order'               => 'Ekyna\Component\Commerce\Order\Entity\Order',
    ];

    /**
     * Initializes the database (once).
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp()
    {
        if (null === static::$_conn) {
            $dbPath = __DIR__ . '/../../../db.sqlite';
            if (file_exists($dbPath)) {
                unlink($dbPath);
            }

            $params = [
                'driver' => 'pdo_sqlite',
                'path'   => $dbPath,
                //'memory' => true,
            ];

            static::$_conn = DriverManager::getConnection($params);
            static::$_conn->getConfiguration()->setSQLLogger(null);

        }

        if (null === static::$_em) {
            $paths = [__DIR__ . '/../../../../../src/Ekyna/Commerce/Bridge/Doctrine/ORM/Resources/mapping'];
            $isDevMode = true;

            $config = Setup::createXMLMetadataConfiguration($paths, $isDevMode);


            $em = EntityManager::create(static::$_conn, $config);

            $classes = [];
            foreach (static::$_classes as $class) {
                array_push($classes, $em->getClassMetadata($class));
            }

            $schemaTool = new SchemaTool($em);
            $schemaTool->dropSchema($classes);
            $schemaTool->createSchema($classes);

            // Load fixtures
            $loader = new Loader();
            $loader->loadFromDirectory(__DIR__ . '/../../../../../src/Ekyna/Commerce/Bridge/Doctrine/Fixtures');
            $purger = new ORMPurger();
            $executor = new ORMExecutor($em, $purger);
            $executor->execute($loader->getFixtures());

            static::$_em = $em;
        }
    }

    /**
     * Sweeps the database tables and clears the EntityManager.
     *
     * @return void
     */
    protected function tearDown()
    {
        if (null === static::$_conn) {
            return;
        }

        if (null !== static::$_em) {
            static::$_em->clear();
        }
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
        if (!array_key_exists($name, static::$_classes)) {
            throw new \InvalidArgumentException("Undefined class '{$name}'.");
        }

        return static::$_classes[$name];
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return static::$_em;
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
        return static::$_em->getRepository($class);
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
