<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

require_once(__DIR__ . '/../lib/db.php');
require_once(__DIR__ . '/../lib/token.php');
require_once(__DIR__ . '/../lib/esi.php');

$member_id = citadeldb_custom_get("member_id");
if ($member_id != NULL) {
	$alliance_cache = citadeldb_alliance_info_get($member_id);
} else {
	die("You are not set member id!\nPlease, run 'php manager.php addmember {yourID}'\nWhere {yourID} is corporation_id or alliance_id.");
}

if ($alliance_cache == NULL) {
	$alliance_data = esi_alliance_get_details($member_id);
	citadeldb_alliance_info_add($member_id, $alliance_data['name'], $alliance_data['ticker']);
	citadeldb_alliance_info_set_member($member_id);
}

$member_corporations = esi_alliance_get_corporations($member_id);

foreach ($member_corporations as $corp_id) {
	$corp_cache = citadeldb_corporation_info_get($corp_id);
	if ($corp_cache == NULL) {
		$corp_data = esi_corporation_get_details($corp_id);
		citadeldb_corporation_info_add($corp_id, $corp_data['name'], $corp_data['ticker']);
		citadeldb_corporation_info_set_alliance($corp_id, $member_id);
	}
}

$corp_cache_member = citadeldb_corporation_info_get_alliance($member_id);
foreach ($corp_cache_member as $corp) {
	if (!in_array($corp['id'],$member_corporations)) {
		citadeldb_corporation_info_unset_alliance($corp['id']);
	}
}

unset($alliance_cache);

$token_char_id = citadeldb_custom_get('contacts_token');
if ($token_char_id != NULL) {
	$access_token = get_token($token_char_id, 'esi-alliances.read_contacts.v1');
} else {
	$access_token = NULL;
}

if ($access_token != NULL) {
	$blue_alliance_ids = citadeldb_alliance_info_getblue_ids();
	$blue_corporation_ids = citadeldb_corporation_info_getblue_ids();
	$contacts_data = esi_alliance_get_contacts($member_id, $access_token);
	foreach ($contacts_data as $contact) {
		if ($contact['standing'] >= 5) {
			switch ($contact['contact_type']) {
				case 'alliance':
					$blue_alliance_id = $contact['contact_id'];
					$blue_alliance_corporations = esi_alliance_get_corporations($blue_alliance_id);
					$blue_alliance_cache = citadeldb_alliance_info_get($blue_alliance_id);
					if ($blue_alliance_cache == NULL) {
						$blue_alliance_data = esi_alliance_get_details($blue_alliance_id);
						citadeldb_alliance_info_add($blue_alliance_id, $blue_alliance_data['name'], $blue_alliance_data['ticker']);
						citadeldb_alliance_info_set_blue($blue_alliance_id);
						unset($blue_alliance_data);
					}
					foreach ($blue_alliance_corporations as $blue_corp_id) {
						$blue_corp_cache = citadeldb_corporation_info_get($blue_corp_id);
						if ($blue_corp_cache == NULL) {
							$blue_corp_data = esi_corporation_get_details($blue_corp_id);
							citadeldb_corporation_info_add($blue_corp_id, $blue_corp_data['name'], $blue_corp_data['ticker']);
							citadeldb_corporation_info_set_alliance($blue_corp_id, $blue_alliance_id);
							citadeldb_corporation_info_set_blue($blue_corp_id);
						}
					}
					unset($blue_alliance_id);
					unset($blue_alliance_cache);
					unset($blue_alliance_corporations);
					unset($blue_corp_cache);
					unset($blue_corp_data);
					continue 2;
				case 'corporation':
					$blue_corporation_id = $contact['contact_id'];
					$blue_corporation_cache = citadeldb_corporation_info_get($blue_corporation_id);
					if ($blue_corporation_cache == NULL) {
						$blue_corporation_data = esi_corporation_get_details($blue_corporation_id);
						citadeldb_corporation_info_add($blue_corporation_id, $blue_corporation_data['name'], $blue_corporation_data['ticker']);
						citadeldb_corporation_info_set_blue($blue_corporation_id);
						unset($blue_corporation_data);
					}
					unset($blue_corporation_id);
					unset($blue_corporation_cache);
					continue 2;
				default:
					continue 2;
			}
		} else {
			switch ($contact['contact_type']) {
				case 'alliance':
					$blue_alliance_id = $contact['contact_id'];
					if (in_array($blue_alliance_id,$blue_alliance_ids)) {
						citadeldb_alliance_info_unset_blue($blue_alliance_id);
					}
					unset($blue_alliance_id);
					continue 2;
				case 'corporation':
					$blue_corporation_id = $contact['contact_id'];
					if (in_array($blue_corporation_id,$blue_corporation_ids)) {
						citadeldb_corporation_info_unset_blue($blue_corporation_id);
					}
					unset($blue_corporation_id);
					continue 2;
				default:
					continue 2;
			}
		}
	}
}

?>