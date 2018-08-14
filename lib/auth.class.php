<?php

require_once(__DIR__ . '/../lib/db.class.php');
require_once(__DIR__ . '/../lib/other.php');

class AuthManager {
	
	function __construct($db_client = null) {
		if ($db_client == null) {
			$this->db = new citadelDB();
		} else {
			$this->db = $db_client;
		}
		$this->config = require __DIR__ . '/../config/app.php';
	}

    function __destruct() {
		unset($this->db);
    }

	function is_member($alliance_id, $corporation_id) {
		$member_id = $this->db->custom_get("member_id");
		if ($alliance_id == $member_id) {
			return true;
		} elseif ($corporation_id == $member_id) {
			return true;
		} else {
			return false;
		}
	}

	function is_blue($alliance_id, $corporation_id) {
		$blue_corporations = $this->db->corporation_info_getblue_ids();
		$blue_alliances = $this->db->alliance_info_getblue_ids();
		if (in_array($corporation_id, $blue_corporations)) {
			return true;
		} elseif (in_array($alliance_id, $blue_alliances)) {
			return true;
		} else {
			return false;
		}
	}

	function corp_role_check($user_id, $group_old, $group_new, $is_member = false, $discord_client = null) {
		$user_groups = $this->db->usergroups_getby_user($user_id);

		if ($is_member) {
			if ($group_old['id'] == $group_new['id']) {
				if (!in_array($group_old['id'],$user_groups)) {
					$this->db->usergroups_add($user_id, $group_old['id']);
				}
			} else {
				//$discord_id = $this->db->discord_get_id($user_id);
				//$discord_client->user_nick_set($discord_id, $discord_nick);
				if (isset($group_new)) {
					if (in_array($group_old['id'],$user_groups)) {
						$this->db->usergroups_delete($user_id, $group_old['id']);
					}
					if (!in_array($group_new['id'],$user_groups)) {
						$this->db->usergroups_add($user_id, $group_new['id']);
					}
				}
			}
		} else {
			if (in_array($group_old['id'],$user_groups)) {
				$this->db->usergroups_delete($user_id, $group_old['id']);
			}
		}
	}

	function auth_role_check($user_id, $is_member = false) {
		$user_groups = $this->db->usergroups_getby_user($user_id);
		$member_group = $this->db->groups_getby_name($this->config['auth']['role_member']);
		$blue_group = $this->db->groups_getby_name($this->config['auth']['role_blue']);

		if ($is_member) {
			if (!in_array($member_group['id'],$user_groups)) {
				$this->db->usergroups_add($user_id, $member_group['id']);
			}
			if (in_array($blue_group['id'],$user_groups)) {
				$this->db->usergroups_delete($user_id, $blue_group['id']);
			}
		} else {
			if (!in_array($blue_group['id'],$user_groups)) {
				$this->db->usergroups_add($user_id, $blue_group['id']);
			}
			if (in_array($member_group['id'],$user_groups)) {
				$this->db->usergroups_delete($user_id, $member_group['id']);
			}
		}
	}

	function character_check_membership($character_id, $esi_data, $cached_data) {
		if ($esi_data['alliance_id'] != $cached_data['alliance_id']) {
			$this->db->character_info_set_alliance($character_id, $esi_data['alliance_id']);
		}
		if ($esi_data['corporation_id'] != $cached_data['corporation_id']) {
			$this->db->character_info_set_corp($character_id, $esi_data['corporation_id']);
		}
	}

	function auth_user_add($character_id, $character_esi, $admins) {
		$character_cache = $this->db->character_info_get($character_id);

		if ($character_cache == null) {
			if ($character_esi['alliance_id'] != null) {
				$this->db->character_info_addfull($character_id, $character_esi['name'], $character_esi['corporation_id'], $character_esi['alliance_id']);
			} else {
				$this->db->character_info_add($character_id, $character_esi['name']);
				$this->db->character_info_set_corp($character_id, $character_esi['corporation_id']);
			}
			$this->db->user_add($character_id);
		} else {
			$this->character_check_membership($character_id, $character_esi, $character_cache);
			$this->db->user_add($character_id);
		}
		if (in_array($character_id, $admins)) {
			$this->db->user_set_admin($character_id);
		}
	}
}