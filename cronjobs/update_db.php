<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

require_once(__DIR__ . '/../lib/db.php');
require_once(__DIR__ . '/../lib/token.php');
require_once(__DIR__ . '/../lib/esi.php');

$token_id = citadeldb_custom_get('contacts_token');
if ($token_id != null) {
	$access_token = get_token($token_id, 'esi-alliances.read_contacts.v1');
}

print_r($access_token."\n");

?>