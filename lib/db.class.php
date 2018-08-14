<?php

class citadelDB {
	
	function __construct() {
		$config = require __DIR__ . '/../config/db.php';
		$this->db = new mysqli($config['url'], $config['user'], $config['pass'], $config['db']['citadel']);
	}

    function __destruct() {
        $this->db->close();
    }

	// Functions for work with `citadel_users`
	function user_add($character_id) {
		$sql = "INSERT INTO `citadel_users` SET character_id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function user_set_admin($character_id) {
		$sql = "UPDATE `citadel_users` SET is_admin = 1 WHERE character_id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function user_unset_admin($character_id) {
		$sql = "UPDATE `citadel_users` SET is_admin = 0 WHERE character_id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function user_activate($character_id) {
		$sql = "UPDATE `citadel_users` SET is_active = 1 WHERE character_id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function user_deactivate($character_id) {
		$sql = "UPDATE `citadel_users` SET is_active = 0 WHERE character_id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function user_get($character_id) {
		$sql = "SELECT id,character_id,is_active,is_admin FROM `citadel_users` WHERE character_id = '$character_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['id'])) {
			return $result;
		} else {
			return null;
		}
	}

	function user_get_by_id($id) {
		$sql = "SELECT character_id FROM `citadel_users` WHERE id = '$id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['character_id'])) {
			return $result['character_id'];
		} else {
			return null;
		}
	}

	function user_admin($id) {
		$sql = "SELECT is_admin FROM `citadel_users` WHERE id = '$id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['is_admin']) && $result['is_admin'] == 1) {
			return true;
		} else {
			return false;
		}
	}

	function user_exist($character_id) {
		$sql = "SELECT * FROM `citadel_users` WHERE character_id = '$character_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['id'])) {
			return true;
		} else {
			return false;
		}
	}

	function users_get_all() {
		$sql = "SELECT * FROM `citadel_users`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function users_get_active() {
		$sql = "SELECT * FROM `citadel_users` WHERE is_active = 1;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function user_get_all_full() {
		$sql = "SELECT
					citadel_users.id,
					eve_character_info.name
				FROM
					citadel_users
				INNER JOIN
					eve_character_info
				ON
					citadel_users.character_id=eve_character_info.id
				WHERE
					is_active = 1
				ORDER BY
					eve_character_info.name ASC;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function user_get_full($id) {
		$sql = "SELECT
					citadel_users.id,
					eve_character_info.name
				FROM
					citadel_users
				INNER JOIN
					eve_character_info
				ON
					citadel_users.character_id=eve_character_info.id
				WHERE
					citadel_users.id = '$id'
				LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	// Functions for work with `citadel_session_keys`
	function session_add($user_id, $session_key, $expire_date) {
		$sql = "INSERT INTO `citadel_session_keys` (user_id, session_key, expire_date) VALUES ('$user_id', '$session_key', '$expire_date');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function session_update($user_id, $session_key) {
		$sql = "UPDATE `citadel_session_keys` SET session_key = '$session_key' WHERE user_id = '$user_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function session_delete($key) {
		$sql = "DELETE FROM `citadel_session_keys` WHERE session_key = '$key';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function session_get($id) {
		$sql = "SELECT * FROM `citadel_session_keys` WHERE user_id = '$id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function session_get_all() {
		$sql = "SELECT * FROM `citadel_session_keys`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function session_get_id($key) {
		$sql = "SELECT user_id FROM `citadel_session_keys` WHERE session_key = '$key' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['user_id'])) {
			return $result['user_id'];
		} else {
			return null;
		}
	}

	function session_get_key($id) {
		$sql = "SELECT session_key FROM `citadel_session_keys` WHERE user_id = '$id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['session_key'])) {
			return $result['session_key'];
		} else {
			return null;
		}
	}

	function session_keycheck($key) {
		$sql = "SELECT user_id,session_key FROM `citadel_session_keys` WHERE session_key = '$key' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['user_id'])) {
			return true;
		} else {
			return false;
		}
	}

	// Functions for work with `eve_alliance_info`
	function alliance_info_add($alliance_id, $name, $ticker) {
		$name = $this->db->real_escape_string($name);
		$ticker = $this->db->real_escape_string($ticker);
		$sql = "INSERT INTO `eve_alliance_info` (id, name, ticker) VALUES ('$alliance_id', '$name', '$ticker');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function alliance_info_get($alliance_id) {
		$sql = "SELECT * FROM `eve_alliance_info` WHERE id = '$alliance_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function alliance_info_getall() {
		$sql = "SELECT * FROM `eve_alliance_info`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function alliance_info_getblue_ids() {
		$sql = "SELECT * FROM `eve_alliance_info` WHERE blue = 1;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			$alliance_ids = array();
			foreach ($result as $alliance) {
				//if ($alliance['blue'] == 1) {
					$alliance_ids[] = $alliance['id'];
				//}
			}
			return $alliance_ids;
		} else {
			return null;
		}
	}

	function alliance_info_set_blue($alliance_id) {
		$sql = "UPDATE `eve_alliance_info` SET blue = '1' WHERE id = '$alliance_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function alliance_info_unset_blue($alliance_id) {
		$sql = "UPDATE `eve_alliance_info` SET blue = '0' WHERE id = '$alliance_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}


	// Functions for work with `eve_corporation_info`
	function corporation_info_add($corporation_id, $name, $ticker) {
		$name = $this->db->real_escape_string($name);
		$ticker = $this->db->real_escape_string($ticker);
		$sql = "INSERT INTO `eve_corporation_info` (id, name, ticker) VALUES ('$corporation_id', '$name', '$ticker');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			printf("Errormessage: %s\n", $this->db->error);
			return null;
		}
	}

	function corporation_info_get($corporation_id) {
		$sql = "SELECT * FROM `eve_corporation_info` WHERE id = '$corporation_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function corporation_info_getall() {
		$sql = "SELECT * FROM `eve_corporation_info`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function corporation_info_getblue_ids() {
		$sql = "SELECT * FROM `eve_corporation_info`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			$corporation_ids = array();
			foreach ($result as $corporation) {
				if ($corporation['blue'] == 1) {
					$corporation_ids[] = $corporation['id'];
				}
			}
			return $corporation_ids;
		} else {
			return null;
		}
	}

	function corporation_info_get_alliance($alliance_id) {
		$sql = "SELECT * FROM `eve_corporation_info` WHERE alliance_id = '$alliance_id';";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function corporation_info_getalliance_ids($alliance_id) {
		$sql = "SELECT * FROM `eve_corporation_info` WHERE alliance_id = '$alliance_id';";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			$corporation_ids = array();
			foreach ($result as $corporation) {
				$corporation_ids[] = $corporation['id'];
			}
			return $corporation_ids;
		} else {
			return null;
		}
	}

	function corporation_info_set_alliance($corporation_id, $alliance_id) {
		if ($alliance_id == 1) {
			$sql = "UPDATE `eve_corporation_info` SET alliance_id = NULL WHERE id = '$corporation_id';";
		} else {
			$sql = "UPDATE `eve_corporation_info` SET alliance_id = '$alliance_id' WHERE id = '$corporation_id';";
		}
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function corporation_info_unset_alliance($corporation_id) {
		$sql = "UPDATE `eve_corporation_info` SET alliance_id = NULL WHERE id = '$corporation_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function corporation_info_set_blue($corporation_id) {
		$sql = "UPDATE `eve_corporation_info` SET blue = '1' WHERE id = '$corporation_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function corporation_info_unset_blue($corporation_id) {
		$sql = "UPDATE `eve_corporation_info` SET blue = '0' WHERE id = '$corporation_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}


	// Functions for work with `eve_character_info`
	function character_info_add($character_id, $name) {
		$name = $this->db->real_escape_string($name);
		$sql = "INSERT INTO `eve_character_info` (id, name) VALUES ('$character_id', '$name');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function character_info_addfull($character_id, $name, $corporation_id, $alliance_id) {
		$name = $this->db->real_escape_string($name);
		$sql = "INSERT INTO `eve_character_info` (id, name, corporation_id, alliance_id) VALUES ('$character_id', '$name', '$corporation_id', '$alliance_id');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function character_info_get($character_id) {
		$sql = "SELECT * FROM `eve_character_info` WHERE id = '$character_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function character_info_getall() {
		$sql = "SELECT * FROM `eve_character_info`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function character_info_set_corp($character_id, $corporation_id) {
		$sql = "UPDATE `eve_character_info` SET corporation_id = '$corporation_id' WHERE id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function character_info_unset_corp($character_id) {
		$sql = "UPDATE `eve_character_info` SET corporation_id = NULL WHERE id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function character_info_set_alliance($character_id, $alliance_id) {
		$sql = "UPDATE `eve_character_info` SET alliance_id = '$alliance_id' WHERE id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function character_info_unset_alliance($character_id) {
		$sql = "UPDATE `eve_character_info` SET alliance_id = NULL WHERE id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}


	// Functions for work with `esi_tokens`
	function token_add($character_id, $access_token, $refresh_token, $scope, $expire_date) {
		$sql = "INSERT INTO `esi_tokens` (character_id, access_token, refresh_token, scope_name, expire_date)
				VALUES ('$character_id', '$access_token', '$refresh_token', '$scope', '$expire_date');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function token_update($character_id, $access_token, $scope, $expire_date) {
		$sql = "UPDATE `esi_tokens` SET access_token = '$access_token', expire_date = '$expire_date'
				WHERE character_id = '$character_id' AND scope_name = '$scope';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function token_updatefull($character_id, $access_token, $refresh_token, $scope, $expire_date) {
		$sql = "UPDATE `esi_tokens` SET access_token = '$access_token', refresh_token = '$refresh_token', expire_date = '$expire_date'
				WHERE character_id = '$character_id' AND scope_name = '$scope';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function token_get($character_id, $scope) {
		$sql = "SELECT access_token, refresh_token, expire_date FROM `esi_tokens`
				WHERE character_id = '$character_id' AND scope_name = '$scope' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['access_token']) && isset($result['refresh_token'])) {
			return $result;
		} else {
			return null;
		}
	}

	function token_del($character_id, $scope) {
		$sql = "DELETE FROM `esi_tokens` WHERE character_id = '$character_id' AND scope_name = '$scope';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function token_del_character($character_id, $scope) {
		$sql = "DELETE FROM `esi_tokens` WHERE character_id = '$character_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	// Functions for work with `custom_storage`
	function custom_add($custom_key, $custom_value) {
		$sql = "INSERT INTO `custom_storage` (custom_key, custom_value) VALUES ('$custom_key', '$custom_value');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function custom_update($custom_key, $custom_value) {
		$sql = "UPDATE `custom_storage` SET custom_value = '$custom_value' WHERE custom_key = '$custom_key';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function custom_get($custom_key) {
		$sql = "SELECT custom_value FROM `custom_storage` WHERE custom_key = '$custom_key' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['custom_value'])) {
			return $result['custom_value'];
		} else {
			return null;
		}
	}

	function custom_del($custom_key) {
		$sql = "DELETE FROM `custom_storage` WHERE custom_key = '$custom_key';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	// Discord_DB Functions
	function discord_add($user_id, $discord_id) {
		$sql = "INSERT INTO `discord_users` (user_id, discord_id) VALUES ('$user_id', '$discord_id');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function discord_get_id($user_id) {
		$sql = "SELECT discord_id FROM `discord_users` WHERE user_id = '$user_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['discord_id'])) {
			return $result['discord_id'];
		} else {
			return null;
		}
	}

	function discord_delete($user_id) {
		$sql = "DELETE FROM `discord_users` WHERE user_id = '$user_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}


	// TeamSpeak_DB Functions
	function teamspeak_add($user_id, $token) {
		$sql = "INSERT INTO `teamspeak_users` (user_id, token) VALUES ('$user_id', '$token');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function teamspeak_get_token($user_id) {
		$sql = "SELECT token FROM `teamspeak_users` WHERE user_id = '$user_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['token'])) {
			return $result['token'];
		} else {
			return null;
		}
	}

	function teamspeak_delete($user_id) {
		$sql = "DELETE FROM `teamspeak_users` WHERE user_id = '$user_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	// phpBB3_DB Functions
	function phpbb3_add($user_id, $username) {
		$sql = "INSERT INTO `phpbb3_users` (user_id, username) VALUES ('$user_id', '$username');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function phpbb3_get_username($user_id) {
		$sql = "SELECT username FROM `phpbb3_users` WHERE user_id = '$user_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['username'])) {
			return $result['username'];
		} else {
			return null;
		}
	}

	function phpbb3_delete($user_id) {
		$sql = "DELETE FROM `phpbb3_users` WHERE user_id = '$user_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}


	// Function for work with `citadel_groups`
	function groups_add($group_name, $color = 0, $discord_hoist = 0) {
		$sql = "INSERT INTO `citadel_groups`
				(name, color, discord_hoist)
				VALUES ('$group_name', '$color', '$discord_hoist');";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	function authgroups_add($group_name, $hidden = 0, $color = 0, $discord_hoist = 0) {
		$sql = "INSERT INTO `citadel_groups`
				(name, discord_enabled, teamspeak_enabled, phpbb3_enabled, color, discord_hoist, hidden)
				VALUES ('$group_name', '1', '1', '1', '$color', '$discord_hoist', '$hidden');";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	function groups_update($group_name, $hidden = 0, $color = 0, $discord_hoist = 0) {
		$sql = "UPDATE `citadel_groups` SET color = '$color', discord_hoist = '$discord_hoist', hidden = '$hidden' WHERE name = '$group_name';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_service_enable($group_name) {
		$sql = "UPDATE `citadel_groups` SET discord_enabled = 1, teamspeak_enabled = 1, phpbb3_enabled = 1 WHERE name = '$group_name';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_service_disable($group_name) {
		$sql = "UPDATE `citadel_groups` SET discord_enabled = 0, teamspeak_enabled = 0, phpbb3_enabled = 0 WHERE name = '$group_name';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_service_disable_by_id($group_id) {
		$sql = "UPDATE `citadel_groups` SET discord_enabled = 0, teamspeak_enabled = 0, phpbb3_enabled = 0 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_disable_all() {
		$sql = "UPDATE `citadel_groups` SET discord_enabled = 0, teamspeak_enabled = 0, phpbb3_enabled = 0;";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_set_discord($group_id) {
		$sql = "UPDATE `citadel_groups` SET discord_enabled = 1 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_unset_discord($group_id) {
		$sql = "UPDATE `citadel_groups` SET discord_enabled = 0 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_set_teamspeak($group_id) {
		$sql = "UPDATE `citadel_groups` SET teamspeak_enabled = 1 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_unset_teamspeak($group_id) {
		$sql = "UPDATE `citadel_groups` SET teamspeak_enabled = 0 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_set_phpbb3($group_id) {
		$sql = "UPDATE `citadel_groups` SET phpbb3_enabled = 1 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_unset_phpbb3($group_id) {
		$sql = "UPDATE `citadel_groups` SET phpbb3_enabled = 0 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function group_set_hidden($group_id) {
		$sql = "UPDATE `citadel_groups` SET hidden = 1 WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function groups_getby_name($group_name) {
		$sql = "SELECT * FROM `citadel_groups` WHERE name = '$group_name' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['id'])) {
			return $result;
		} else {
			return null;
		}
	}

	function groups_getby_id($group_id) {
		$sql = "SELECT * FROM `citadel_groups` WHERE id = '$group_id' LIMIT 1;";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['id'])) {
			return $result;
		} else {
			return null;
		}
	}

	function groups_getall() {
		$sql = "SELECT * FROM `citadel_groups`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}
	
	function groups_getall_nothidden() {
		$sql = "SELECT * FROM `citadel_groups` WHERE hidden = 0;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function groups_deleteby_id($group_id) {
		$sql = "DELETE FROM `citadel_groups` WHERE id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	function groups_deleteby_name($group_name) {
		$sql = "DELETE FROM `citadel_groups` WHERE name = '$group_name';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}


	// Function for work with `citadel_user_groups`
	function usergroups_add($user_id, $group_id) {
		$sql = "INSERT INTO `citadel_user_groups` (user_id, group_id) VALUES ('$user_id', '$group_id');";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function usergroups_getby_user($user_id) {
		$sql = "SELECT * FROM `citadel_user_groups` WHERE user_id = '$user_id';";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			$groups = array();
			foreach ($result as $group) {
				$groups[] = $group['group_id'];
			}
			return $groups;
		} else {
			return null;
		}
	}

	function usergroups_getfullby_user($user_id) {
		$sql = "SELECT
					citadel_user_groups.group_id,
					citadel_groups.name,
					citadel_groups.teamspeak_enabled,
					citadel_groups.discord_enabled,
					citadel_groups.phpbb3_enabled
				FROM
					citadel_user_groups
				INNER JOIN
					citadel_groups
				ON
					citadel_user_groups.group_id=citadel_groups.id
				WHERE
					citadel_user_groups.user_id='$user_id'
				ORDER BY
					citadel_user_groups.group_id ASC;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			return $result;
		} else {
			return null;
		}
	}

	function usergroups_getby_group($group_id) {
		$sql = "SELECT * FROM `citadel_user_groups` WHERE group_id = '$group_id';";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			$users = array();
			foreach ($result as $user) {
				$users[] = $user['user_id'];
			}
			return $users;
		} else {
			return null;
		}
	}

	function usergroups_delete($user_id, $group_id) {
		$sql = "DELETE FROM `citadel_user_groups` WHERE user_id = '$user_id' AND group_id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	function usergroups_deleteby_group($group_id) {
		$sql = "DELETE FROM `citadel_user_groups` WHERE group_id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}

	//function usergroups_deleteby_name($user_id) {
	function usergroups_deleteby_user($user_id) {
		$sql = "DELETE FROM `citadel_user_groups` WHERE user_id = '$user_id';";
		if ($this->db->query($sql) === TRUE) {
			return null;
		} else {
			return null;
		}
	}
}