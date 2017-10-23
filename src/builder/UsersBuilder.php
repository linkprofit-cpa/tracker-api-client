<?php

namespace linkprofit\trackerApiClient\builder;


class UsersBuilder extends TrackerBuilder
{
    protected $entity = 'users';

    public function limit($limit)
    {
        $this->params['limit'] = $limit;

        return $this;
    }

    protected function handle()
    {
        return $this->data;
    }
}