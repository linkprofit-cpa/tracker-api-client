<?php
/**
 * Created by PhpStorm.
 * User: travkin
 * Date: 27.02.18
 * Time: 13:13
 */

namespace linkprofit\trackerApiClient\tests;

use linkprofit\trackerApiClient\Connection;
use linkprofit\trackerApiClient\Request;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    protected function setUp()
    {
        $this->connection = new Connection();
        $this->connection->apiUrl = API_URL;
        $this->connection->login = API_LOGIN;
        $this->connection->password = API_PASSWORD;
        $this->connection->isAdmin = true;
        $this->connection->cacheConnect = true;
    }

    /**
     * @expectedException \linkprofit\trackerApiClient\exceptions\ConnectionConfigException
     */
    public function test__constructOnFail()
    {
        (new Connection())->connect();
    }
    public function test__construct()
    {
        $this->connection->isAdmin = true;
        $this->assertInstanceOf(Connection::class, $this->connection);
        $this->assertInstanceOf(\Memcached::class, $this->connection->getCacheObject());
    }

    /**
     * @depends test__construct
     */
    public function testGetApiUrl()
    {
        $this->assertEquals(API_URL, $this->connection->getApiUrl());
        $this->assertInternalType('string', $this->connection->getApiUrl());
    }
    /**
     * @depends test__construct
     */
    public function testGetCacheObject()
    {
        $this->assertAttributeInstanceOf(\Memcached::class, 'cache', $this->connection);

    }
    /**
     * @depends test__construct
     */
    public function testRequest()
    {
        $this->assertInstanceOf(Request::class, $this->connection->request());
    }
    /**
     * @depends test__construct
     */
    public function testConnect()
    {
        $this->connection->cacheConnect = false;
        $this->assertInternalType('string', $this->connection->connect());
        $this->connection->cacheConnect = true;
        $this->assertInternalType('string', $this->connection->connect());
        $this->assertInternalType('string', $this->connection->connect());
    }
    /**
     * @expectedException \linkprofit\trackerApiClient\exceptions\ResponseException
     */
    public function testConnectWithResponseException(){
        $this->connection->cacheConnect = false;
        $this->connection->isAdmin = false;
        $this->connection->connect();
    }
    /**
     * @expectedException \linkprofit\trackerApiClient\exceptions\ConnectionException
     */
    public function testConnectWithConnectionException(){
        $this->connection->cacheConnect = false;
        $this->connection->isAdmin = true;
        $this->connection->connect(9999);
    }

    /**
     * @depends test__construct
     */
    public function testGetAuthToken()
    {
        $this->connection->cacheConnect = false;
        $this->assertInternalType('string', $this->connection->getAuthToken());
        $this->connection->cacheConnect = true;
        $token = $this->connection->getAuthToken();
        $this->assertInternalType('string', $token);
        $this->assertEquals($token, $this->connection->getAuthToken());
        $this->connection->getCacheObject()->delete(Connection::API_SESSION_KEY);
        $this->assertNotEquals($token, $this->connection->getAuthToken());
    }
}
