<?php
require_once 'vendor/autoload.php';

use linkprofit\trackerApiClient\Connection;

$connection = new Connection();
$connection->apiUrl = '';
$connection->login = '';
$connection->password = '';

$offers = $connection->request()->get('offers');
