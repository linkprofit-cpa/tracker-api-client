<?php

namespace linkprofit\trackerApiClient\builder;


class UsersBuilder extends TrackerBuilder
{
    protected $entity = 'users';
    private $fieldsVars = [
        "userid", "refid", "username", "apikey",
        "firstname", "lastname", "middlename",
        "topname", "phone", "city", "regip",
        "dateinserted", "datelastlogin",
        "status", "commissionrate", "managerid",
    ];

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit = 100)
    {
        $this->params['limit'] = (integer)$limit;

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
            $this->params['fields'] = array_values(array_intersect(array_map('strtolower',$fields),$this->fieldsVars));

        return $this;
    }

    /**
     * @param array $statuses ['P','A','D']
     * @return $this
     */
    public function statuses($statuses = [])
    {
        if (!empty($statuses))
            $this->params['statuses'] = array_values(array_intersect(array_map('strtoupper',$statuses),['A','P','D']));
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