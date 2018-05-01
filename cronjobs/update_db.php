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
		die("[".date("Y-m-d H:i:s", time())."] EVE ESI not online\n");
	}

	//print_r("[".date("Y-m-d H:i:s", time())."] Checking owner alliance.\n");
	//$eveinfo_manager->check_alliance($member_id);
	print_r("[".date("Y-m-d H:i:s", time())."] Checking owner corporations.\n");
	$eveinfo_manager->check_alliance_corporations($member_id, $config['auth']);

	if ($access_token != null) {
		print_r("[".date("Y-m-d H:i:s", time())."] Checking blue standings.\n");
		$contacts = $esi_client->alliance_get_contacts($member_id);
		if ($contacts != null) {
			print_r("[".date("Y-m-d H:i:s", time())."] Checking blue alliances.\n");
			$eveinfo_manager->check_blue_alliances($contacts, $config['auth']);
			print_r("[".date("Y-m-d H:i:s", time())."] Checking blue corporations.\n");
			$eveinfo_manager->check_blue_corporations($contacts);
		}
	}

	unset($db_client, $eveinfo_manager, $esi_client);
} else {
	unset($db_client);
	die("[".date("Y-m-d H:i:s", time())."] You are not set member id!\nPlease, run 'php manager.php addmember {yourID}'\nWhere {yourID} is corporation_id or alliance_id.");
}

?>