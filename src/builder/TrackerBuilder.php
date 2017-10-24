<?php
namespace linkprofit\trackerApiClient\builder;

use linkprofit\trackerApiClient\Connection;
use Symfony\Component\Cache\Simple\FilesystemCache;

abstract class TrackerBuilder
{
    /**
     * @var int 60*60*6 seconds
     */
    public    $cacheDuration = 21600;
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
     * @param $connection Connection;
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

        $cache = new FilesystemCache();

        $key = $this->entity . md5(json_encode($this->params));
        if (!$cache->has($key)) {
            $this->data = json_decode($this->connection->request()->get($this->entity, $this->params), 1);
            $cache->set($key, $this->data, $this->cacheDuration);
        } else {
            $this->data = $cache->get($key);
        }

        return $this->handle();
    }

    /**
     * @return mixed
     */
    abstract protected function handle();
}