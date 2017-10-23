<?php
namespace linkprofit\trackerApiClient\builder;

use linkprofit\trackerApiClient\Connection;
use Symfony\Component\Cache\Simple\FilesystemCache;

abstract class TrackerBuilder
{
    protected $data;
    protected $entity;
    protected $params = [];

    /**
     * @param $connection Connection;
     * @return mixed
     */
    public function get($connection)
    {
        $cache = new FilesystemCache();

        $key = $this->entity . implode('', $this->params);
        if (!$cache->has($key)) {
            $this->data = json_decode($connection->request()->get($this->entity, $this->params), 1);
            $cache->set($key, $this->data, 60 * 60 * 6);
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