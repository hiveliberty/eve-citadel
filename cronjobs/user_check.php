<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use RestCord\DiscordClient;

$config = require __DIR__ . '/../config/app.php';

require_once(__DIR__ . '/../lib/other.php');
require_once(__DIR__ . '/../lib/auth.php');
require_once(__DIR__ . '/../lib/db.php');
require_once(__DIR__ . '/../lib/esi.php');
require_once(__DIR__ . '/../lib/ts3.php');

$users = citadeldb_users_getall_active();
foreach ($users as $user) {
	check_user($user);
}

?>