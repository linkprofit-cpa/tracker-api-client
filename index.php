<?php
require_once 'vendor/autoload.php';

use linkprofit\trackerApiClient\Connection;

$connection = new Connection();
$connection->apiUrl = '';
$connection->login = '';
$connection->password = '';
$connection->isAdmin = true;

$users = new \linkprofit\trackerApiClient\builder\UsersBuilder();
$users->get($connection);

var_dump($users);
