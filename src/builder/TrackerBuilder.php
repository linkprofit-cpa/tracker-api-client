<?php
namespace linkprofit\trackerApiClient\builder;

use linkprofit\trackerApiClient\Connection;
use linkprofit\trackerApiClient\exceptions\ConnectionException;
use linkprofit\trackerApiClient\exceptions\RequestException;
use linkprofit\trackerApiClient\exceptions\ResponseException;
use Symfony\Component\Cache\Simple\FilesystemCache;

abstract class TrackerBuilder
{
    /**
     * @var int 60*60*6 seconds
     */
    protected $cacheDuration = 21600;
    protected $data;
    protected $entity;
    protected $params = [];
    protected $connection = null;

    /**
     * TrackerBuilder constructor.
     * @param $connection
     */
    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * @param int|bool $cacheDuration для отключения кэширования запросов используйте <b>false</b>
     */
    public function setCacheDuration($cacheDuration = 21600)
    {
        $this->cacheDuration = $cacheDuration;
    }

    /**
     * @param $connection Connection;
     * @throws RequestException
     * @throws ResponseException
     * @throws ConnectionException
     * @return mixed
     */
    public function get(Connection $connection = null)
    {
        if (!empty($connection)) {
            $this->connection = $connection;
        }
        if ($this->cacheDuration === false) {
            $this->data = json_decode($this->connection->request()->get($this->entity, $this->params), 1);
            return $this->handle();
        }

        $key = $this->entity . md5(json_encode($this->params));
        if (!$this->connection->getCacheObject()->has($key)) {
            $this->data = json_decode($this->connection->request()->get($this->entity, $this->params), 1);
            $this->connection->getCacheObject()->set($key, $this->data, $this->cacheDuration);
        } else {
            $this->data = $this->connection->getCacheObject()->get($key);
        }

        return $this->handle();
    }

    /**
     * @return mixed
     */
    abstract protected function handle();
}