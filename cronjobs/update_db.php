<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

require_once(__DIR__ . '/../lib/db.class.php');
require_once(__DIR__ . '/../lib/other.php');
require_once(__DIR__ . '/../lib/token.php');
require_once(__DIR__ . '/../lib/eveinfo.class.php');
require_once(__DIR__ . '/../lib/esi.class.php');

$logger = get_logger("update_db", "INFO");
$db_client = new citadelDB();
$eveinfo_manager = new EveInfoManager($db_client);
$member_id = $db_client->custom_get("member_id");

if ($member_id != null) {
	$contacts_token = $db_client->custom_get('contacts_token');
	if ($contacts_token != null) {
		$access_token = get_token($contacts_token, 'esi-alliances.read_contacts.v1');
	} else {
		$access_token = null;
	}

	$esi_client = new ESIClient("tranquility", $access_token);
	if (!$esi_client->is_online()) {
		$logger->info('EVE ESI not online. Stop the task.');
		die();
	}

	// $logger->info('Checking owner alliance.');
	//$eveinfo_manager->check_alliance($member_id);

	$logger->info('Checking owner corporations.');
	$eveinfo_manager->check_alliance_corporations($member_id, $config['auth']);

	if ($access_token != null) {
		$logger->info('Checking blue standings.');
		$contacts = $esi_client->alliance_get_contacts($member_id);
		if ($contacts != null) {

			$logger->info('Checking blue alliances.');
			$eveinfo_manager->check_blue_alliances($contacts, $config['auth']);

			$logger->info('Checking blue corporations.');
			$eveinfo_manager->check_blue_corporations($contacts);

		}
	}

	unset($db_client, $eveinfo_manager, $esi_client);
} else {
	unset($db_client, $logger, $eveinfo_manager);
	$logger->info("You are not set member id!\nPlease, run 'php manager.php addmember {yourID}', where {yourID} is corporation_id or alliance_id.");
	die();
}
?>