<?php
namespace linkprofit\trackerApiClient;

use linkprofit\trackerApiClient\exceptions\ConnectionConfigException;
use linkprofit\trackerApiClient\exceptions\ConnectionException;
use linkprofit\trackerApiClient\exceptions\RequestException;
use linkprofit\trackerApiClient\exceptions\ResponseException;
use Symfony\Component\Cache\Simple\AbstractCache;
use Symfony\Component\Cache\Simple\MemcachedCache;

class Connection
{
    public $apiUrl = '';
    public $login = '';
    public $password = '';
    public $connectionTryLimit = 3;
    public $isAdmin = false;
    /**
     * @var bool
     */
    public $cacheConnect = true;

    /**
     * @var \Memcached
     */
    protected $cache;

    const API_SESSION_KEY = 'apiAuth';

    /**
     * Инициализирует компонент
     * @throws ConnectionConfigException
     */
    public function __construct()
    {
        $this->getCacheObject();
    }

    public function getCacheObject(){
        if(empty($this->cache)){
            $this->cache = new \Memcached();
            $this->cache->addServer('localhost', 11211);
        }
        return $this->cache;
    }

    /**
     * Проверяет, установлены ли обязательные параметры для компонента
     * @throws ConnectionConfigException
     */
    protected function checkConfig()
    {
        $params = ['apiUrl', 'login', 'password'];

        foreach ($params as $param) {
            if (empty($this->{$param}) || !is_string($this->{$param})) {
                throw new ConnectionConfigException('You must set param '.$param);
            }
        }
    }

    /**
     * Возвращает объект Request, при помощи которого можно осуществлять запросы к api
     * @return Request
     */
    public function request()
    {
        $request = new Request();
        $request->connection = $this;

        return $request;
    }

    /**
     * Вовзращает url api
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Возвращает authToken для обращения к api
     * @return string
     * @throws ConnectionException
     * @throws RequestException
     * @throws ResponseException
     */
    public function getAuthToken()
    {
        if ($this->cacheConnect !== true) {
            $authToken = $this->connect();
            return $authToken;
        }

        if ($authToken = $this->cache->get(self::API_SESSION_KEY)) {
            return $authToken;
        }

        $authToken = $this->connect();

        $this->cache->set(self::API_SESSION_KEY, $authToken);

        return $authToken;
    }

    /**
     * Обращается на api для получения authToken, вовзращает authToken.
     * @param int $try
     * @return string authToken
     * @throws ConnectionConfigException
     * @throws ConnectionException
     * @throws RequestException
     * @throws ResponseException
     */
    public function connect($try = 1)
    {
        $this->checkConfig();
        if ($try > $this->connectionTryLimit) {
            throw new ConnectionException("Can't authorize! The reconnection limit exhausted");
        }

        $request = new Request();
        $request->connection = $this;

        if ($this->isAdmin === false) {
            $authRoute = 'userAuth';
        } else {
            $authRoute = 'administratorAuth';
        }

        $jsonApiResponse = $request->get($authRoute, [
            'userName' => $this->login,
            'userPassword' => $this->password,
        ]);

        $apiResponse = json_decode($jsonApiResponse);

        if (empty($apiResponse->authToken))
            throw new ConnectionException("Can't authorize! Check user name or password!");

        return $apiResponse->authToken;
    }
}