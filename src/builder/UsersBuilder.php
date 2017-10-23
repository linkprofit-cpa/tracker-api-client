<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 23.10.17
 * Time: 14:58
 */

namespace linkprofit\trackerApiClient\builder;


class UsersBuilder extends TrackerBuilder
{
    protected $entity = 'users';

    protected function handle()
    {
        return $this->data;
    }
}