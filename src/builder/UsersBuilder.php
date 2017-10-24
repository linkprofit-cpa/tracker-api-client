<?php

namespace linkprofit\trackerApiClient\builder;


class UsersBuilder extends TrackerBuilder
{
    protected $entity = 'users';

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit = 100)
    {
        if(!empty($limit))
            $this->params['limit'] = $limit;

        return $this;
    }

    /**
     * @param array $fields
     * [
     *  "userId","refId","userName",
     *  "apiKey","firstName","lastName","middleName",
     *  "topName","phone","city","regIp","dateInserted",
     *  "dateLastLogin","status","commissionRate","managerId"
     * ]
     * @return $this
     */
    public function fields($fields = [])
    {
        if(!empty($fields))
            $this->params['fields'] = $fields;

        return $this;
    }

    /**
     * @param array $statuses ['P','A','D']
     * @return $this
     */
    public function statuses($statuses = [])
    {
        if (!empty($statuses))
            $this->params['statuses'] = array_intersect(array_map('strtoupper',$statuses),['A','P','D']);
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