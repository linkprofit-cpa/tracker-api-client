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

    public function fields($fields = [])
    {
        $this->params['fields'] = $fields;

        return $this;
    }

    protected function handle()
    {
        foreach ($this->data['data'] as &$user){
            $user = array_filter($user);
        }
        return $this->data['data'];
    }
}