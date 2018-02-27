<?php
/**
 * Created by PhpStorm.
 * User: travkin
 * Date: 27.02.18
 * Time: 13:04
 */

namespace linkprofit\trackerApiClient\tests;

use linkprofit\trackerApiClient\builder\UsersBuilder;
use linkprofit\trackerApiClient\Connection;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Node\Builder;

class UsersBuilderTest extends TestCase
{
    /**
     * @var UsersBuilder
     */
    private $builder;

    protected function setUp()
    {
        $connection = new Connection();
        $connection->apiUrl = API_URL;
        $connection->login = API_LOGIN;
        $connection->password = API_PASSWORD;
        $connection->isAdmin = true;
        $connection->cacheConnect = true;
        $this->builder = new UsersBuilder($connection);
    }

    public function testStatuses()
    {
        $this->builder->statuses(['A']);
        $this->assertAttributeInternalType('array', 'params', $this->builder);
        $params = $this->getPrivateProperty('params');
        $this->assertInternalType('array', $params);
        $this->assertArrayHasKey('statuses', $params);
        $this->assertEquals(['A'], $params['statuses']);
        $this->builder->statuses(['P','D']);
        $this->assertAttributeInternalType('array', 'params', $this->builder);
        $params = $this->getPrivateProperty('params');
        $this->assertInternalType('array', $params);
        $this->assertArrayHasKey('statuses', $params);
        $this->assertEquals(['P','D'], $params['statuses']);
    }



    public function testSetCacheDuration()
    {
        $this->builder->setCacheDuration(1);
        $this->assertEquals(1, $this->getPrivateProperty('cacheDuration'));
    }

    /**
     * @expectedException \linkprofit\trackerApiClient\exceptions\ConnectionException
     */
    public function testGetWithoutConnection()
    {
        (new UsersBuilder())->get();
    }

    public function testGet()
    {
        $users = $this->builder->get();
        $this->assertInternalType('array', $users);
        $this->assertArrayHasKey('0', $users);
        $this->assertInternalType('array', $users[0]);
        $this->assertArrayHasKey('userId', $users[0]);
        /**
         * @var Connection
         */
        $connection = $this->getPrivateProperty('connection');
        $connection->getCacheObject()->delete('usersd751713988987e9331980363e24189ce');
        $users = $this->builder->get();
        $this->assertInternalType('array', $users);
        $this->assertArrayHasKey('0', $users);
        $this->assertInternalType('array', $users[0]);
        $this->assertArrayHasKey('userId', $users[0]);

        $connection = new Connection();
        $connection->apiUrl = API_URL;
        $connection->login = API_LOGIN;
        $connection->password = API_PASSWORD;
        $connection->isAdmin = true;
        $connection->cacheConnect = true;
        $this->builder->setCacheDuration(false);
        $users = $this->builder->get($connection);
        $this->assertInternalType('array', $users);
        $this->assertArrayHasKey('0', $users);
        $this->assertInternalType('array', $users[0]);
        $this->assertArrayHasKey('userId', $users[0]);
    }

    public function testFields()
    {
        $this->builder->fields(["userid","refid"]);

        $this->assertAttributeInternalType('array', 'params', $this->builder);
        $params = $this->getPrivateProperty('params');
        $this->assertInternalType('array', $params);
        $this->assertArrayHasKey('fields', $params);
        $this->assertEquals(["userid","refid"], $params['fields']);

        $users = $this->builder->limit(1)->get();
        $this->assertArrayHasKey('0', $users);
        $this->assertArrayNotHasKey('1', $users);
        $this->assertArrayHasKey('userId', $users[0]);
        $this->assertArrayNotHasKey('status', $users[0]);
    }

    public function testLimit()
    {
        $users = $this->builder->limit(10)->get();
        $this->assertInternalType('array', $users);
        $this->assertEquals(10, count($users));
        $users = $this->builder->limit(13)->get();
        $this->assertInternalType('array', $users);
        $this->assertEquals(13, count($users));
    }

//    /**
//     * @param $name
//     * @param array $args
//     * @return mixed
//     * @throws \ReflectionException
//     */
//    protected function invokePrivateMethod($name,$args = []) {
//        $class = new \ReflectionClass(UsersBuilder::class);
//        $method = $class->getMethod($name);
//        $method->setAccessible(true);
//        return $method->invokeArgs($this->builder, $args);
//    }

    /**
     * @param $propertyName
     * @return mixed
     * @throws \ReflectionException
     */
    public function getPrivateProperty($propertyName ) {
        $reflector = new \ReflectionClass( UsersBuilder::class );
        $property = $reflector->getProperty( $propertyName );
        $property->setAccessible( true );

        return $property->getValue($this->builder);
    }
}
