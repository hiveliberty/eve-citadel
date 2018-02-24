<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

require_once(__DIR__ . '/../lib/db.php');
require_once(__DIR__ . '/../lib/token.php');
require_once(__DIR__ . '/../lib/esi.php');

function member_exist() {
	$member_id = citadeldb_custom_get("member_id");
	if ($member_id != NULL) {
		return true;
	} else {
		return false;
	}
}

function contacts_alliance_ids($contacts, $min_standing) {
	$alliance_ids = array();
	foreach ($contacts as $contact) {
		if ($contact['standing'] >= $min_standing) {
			if ($contact['contact_type'] == 'alliance') {
				$alliance_ids[] = $contact['contact_id'];
			}
			//switch ($contact['contact_type']) {
			//	case 'alliance':
			//		$alliance_ids[] = $contact['contact_id'];
			//		continue 2;
			//	default:
			//		continue 2;
			//}
		}
	}
	return $alliance_ids;
}

function contacts_corporation_ids($contacts, $min_standing) {
	$corporation_ids = array();
	foreach ($contacts as $contact) {
		if ($contact['standing'] >= $min_standing) {
			if ($contact['contact_type'] == 'corporation') {
				$corporation_ids[] = $contact['contact_id'];
			}
			//switch ($contact['contact_type']) {
			//	case 'corporation':
			//		$corporation_ids[] = $contact['contact_id'];
			//		continue 2;
			//	default:
			//		continue 2;
			//}
		}
	}
	return $corporation_ids;
}

function check_alliance($alliance_id) {
	$alliance_cache = citadeldb_alliance_info_get($alliance_id);

	if ($alliance_cache == NULL) {
		$alliance_data = esi_alliance_get_details($alliance_id);
		citadeldb_alliance_info_add($alliance_id, $alliance_data['name'], $alliance_data['ticker']);
	}
}

function check_corporation($corporation_id) {
	$corporation_cache = citadeldb_corporation_info_get($corporation_id);

	if ($corporation_cache == NULL) {
		$corporation_data = esi_corporation_get_details($corporation_id);
		citadeldb_corporation_info_add($corporation_id, $corporation_data['name'], $corporation_data['ticker']);
	}
}

function check_alliance_corporations($alliance_id) {
	$corporation_cache_ids = citadeldb_corporation_info_getalliance_ids($alliance_id);
	$alliance_corporations = esi_alliance_get_corporations($alliance_id);

	if ($corporation_cache_ids != $alliance_corporations) {
		if ($corporation_cache_ids != null) {
			foreach ($corporation_cache_ids as $corporation_cache_id) {
				if (!in_array($corporation_cache_id,$alliance_corporations)) {
					citadeldb_corporation_info_unset_alliance($corporation_cache_id);
				}
			}
		}

		foreach ($alliance_corporations as $corporation_id) {
			check_corporation($corporation_id);
			citadeldb_corporation_info_set_alliance($corporation_id, $alliance_id);
		}
	}
}

function check_blue_alliances($contacts) {
	$alliance_cache_ids = citadeldb_alliance_info_getblue_ids();
	$contacts_ids = contacts_alliance_ids($contacts, 5);

	foreach ($alliance_cache_ids as $alliance_id) {
		if (!in_array($alliance_id,$contacts_ids)) {
			citadeldb_alliance_info_unset_blue($alliance_id);
		}
		check_alliance_corporations($alliance_id);
	}

	foreach ($contacts_ids as $alliance_id) {
		if (!in_array($alliance_id,$alliance_cache_ids)) {
			check_alliance($alliance_id);
			citadeldb_alliance_info_set_blue($alliance_id);
			check_alliance_corporations($alliance_id);
		}
	}
}

function check_blue_corporations($contacts) {
	$corporation_cache_ids = citadeldb_corporation_info_getblue_ids();
	$contacts_ids = contacts_corporation_ids($contacts, 5);

	foreach ($corporation_cache_ids as $corporation_id) {
		if (!in_array($corporation_id,$contacts_ids)) {
			citadeldb_corporation_info_unset_blue($corporation_id);
		}
	}

	foreach ($contacts_ids as $corporation_id) {
		if (!in_array($corporation_id,$corporation_cache_ids)) {
			check_corporation($corporation_id);
			citadeldb_corporation_info_set_blue($corporation_id);
		}
	}
}



// Main logic
if (member_exist()) {
	$member_id = citadeldb_custom_get("member_id");
	check_alliance($member_id);
	check_alliance_corporations($member_id);

	$contacts_token = citadeldb_custom_get('contacts_token');
	if ($contacts_token != NULL) {
		$access_token = get_token($contacts_token, 'esi-alliances.read_contacts.v1');
	} else {
		$access_token = NULL;
	}

	if ($access_token != NULL) {
		$contacts = esi_alliance_get_contacts($member_id, $access_token);
		check_blue_alliances($contacts);
		check_blue_corporations($contacts);
	}
} else {
	die("You are not set member id!\nPlease, run 'php manager.php addmember {yourID}'\nWhere {yourID} is corporation_id or alliance_id.");
}

?>