<?php

require_once(__DIR__ . '/../lib/db.php');
require_once(__DIR__ . '/../lib/esi.php');

function auth_check_member($alliance_id, $corporation_id) {
	$member_id = citadeldb_custom_get("member_id");
	if ($alliance_id == $member_id) {
		return true;
	} elseif ($corporation_id == $member_id) {
		return true;
	} else {
		return false;
	}
}

function auth_check_blue($alliance_id, $corporation_id) {
	$blue_corporations = citadeldb_corporation_info_getblue_ids();
	$blue_alliances = citadeldb_alliance_info_getblue_ids();
	if (in_array($corporation_id, $blue_corporations)) {
		return true;
	} elseif (in_array($alliance_id, $blue_alliances)) {
		return true;
	} else {
		return false;
	}
}

function auth_addmember($character_id, $character_data, $admins) {
	$character_info = citadeldb_character_info_get($character_id);
	if ($character_info == null) {
		if ($character_data['alliance_id'] != 1) {
			citadeldb_character_info_addfull($character_id, $character_data['name'], $character_data['corporation_id'], $character_data['alliance_id']);
		} else {
			citadeldb_character_info_add($character_id, $character_data['name']);
			citadeldb_character_info_set_corp($character_id, $character_data['corporation_id']);
		}
		citadeldb_users_add($character_id);
	} else {
		if ($character_info['corporation_id'] != $character_data['corporation_id']) {
			citadeldb_character_info_set_corp($character_id, $character_data['corporation_id']);
		}
		if ($character_info['alliance_id'] != $character_data['alliance_id']) {
			citadeldb_character_info_set_alliance($character_id, $character_data['alliance_id']);
		}
		citadeldb_users_add($character_id);
	}
	if (in_array($character_id, $admins)) {
		citadeldb_users_set_admin($character_id);
	}
}

function check_user($user) {
	$character_id = $user['character_id'];
	$character_cache = citadeldb_character_info_get($character_id);
	$character_esi = esi_character_get_details($character_id);
	
	if ($character_cache['corporation_id'] != $character_esi['corporation_id']) {
		$corporation_cache = citadeldb_corporation_info_get($character_esi['corporation_id']);
		if ($corporation_cache == NULL) {
			citadeldb_character_info_unset_corp($character_id);
		} else {
			citadeldb_character_info_set_corp($character_id, $character_esi['corporation_id']);
		}
	}
	if ($character_cache['alliance_id'] != $character_esi['alliance_id']) {
		if ($character_esi['alliance_id'] = 1) {
			citadeldb_character_info_unset_alliance($character_id);
		} else {
			$alliance_cache = citadeldb_alliance_info_get($alliance_id);
			if ($alliance_cache == NULL) {
				citadeldb_character_info_unset_alliance($character_id);
			} else {
				citadeldb_character_info_set_alliance($character_id, $character_esi['alliance_id']);
			}
		}
	}
}