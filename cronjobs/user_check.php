<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

require_once(__DIR__ . '/../lib/db.class.php');
require_once(__DIR__ . '/../lib/other.php');
require_once(__DIR__ . '/../lib/auth.class.php');
require_once(__DIR__ . '/../lib/esi.class.php');
require_once(__DIR__ . '/../lib/ts3.class.php');
require_once(__DIR__ . '/../lib/phpbb3.class.php');
require_once(__DIR__ . '/../lib/discord.class.php');

$db_client = new citadelDB();
$auth_manager = new AuthManager($db_client);
$esi_client = new ESIClient();
if (!$esi_client->is_online()) {
	die("[".date("Y-m-d H:i:s", time())."] EVE ESI not online\n");
}
if ($config['services']['ts3_enabled']) {
	$ts_client = new ts3client();
}
if ($config['services']['phpbb3_enabled']) {
	$phpbb3_client = new phpBB3client();
}
if ($config['services']['discord_enabled']) {
	$discord_client = new DiscordCitadelClient();
}

$users = $db_client->users_get_active();

print_r("[".date("Y-m-d H:i:s", time())."] Started users checking.\n");

foreach(array_chunk($users, 5, true) as $users_chunk) {
	foreach ($users_chunk as $user) {
		$character_id = $user['character_id'];
		$character_esi = $esi_client->character_get_details($character_id);

		if (!isset($character_esi) && $character_esi == null) {
			print_r("[".date("Y-m-d H:i:s", time())."] ESI is not online. Stop user checking.\n");
			break 2;
		}

		$character_cache = $db_client->character_info_get($character_id);
		$alliance_esi_id = $character_esi['alliance_id'];
		$alliance_cached_id = $character_cache['alliance_id'];
		$corp_esi_id = $character_esi['corporation_id'];
		$corporation_esi = $esi_client->corporation_get_details($corp_esi_id);
		$corp_cached_id = $character_cache['corporation_id'];
		$corporation_cache = $db_client->corporation_info_get($corp_cached_id);
		$group_new_name = corp_group_name($corporation_esi['ticker']);
		$group_old_name = corp_group_name($corporation_cache['ticker']);

		$group_new = $db_client->groups_getby_name($group_new_name);
		$group_old = $db_client->groups_getby_name($group_old_name);

		if ($auth_manager->is_member($alliance_esi_id, $corp_esi_id)) {
			$auth_manager->character_check_membership($character_id, $character_esi, $character_cache);
			$auth_manager->auth_role_check($user['id'], true);
			$auth_manager->corp_role_check($user['id'], $group_old, $group_new, true);
		} elseif ($auth_manager->is_blue($alliance_esi_id, $corp_esi_id)) {
			$auth_manager->character_check_membership($character_id, $character_esi, $character_cache);
			$auth_manager->auth_role_check($user['id'], false);
			$auth_manager->corp_role_check($user['id'], $group_old, $group_new, false);
		} else {
			print_r("[".date("Y-m-d H:i:s", time())."] Now user {$character_esi['name']} is not member or blue. Delete all roles.\n");

			if ($config['services']['ts3_enabled']) {
				$ts_token = $db_client->teamspeak_get_token($user['id']);
				if ($ts_token != null) {
					$db_client->teamspeak_delete($user['id']);
					$ts_client->user_del($character_id, $ts_token);
				}
			}

			if ($config['services']['discord_enabled']) {
				$discord_id = $db_client->discord_get_id($user['id']);
				if ($discord_id != null) {
					$discord_client->user_del($discord_id);
					$db_client->discord_delete($user['id']);
				}
			}

			if ($config['services']['phpbb3_enabled']) {
				$phpbb3_username = $db_client->phpbb3_get_username($user['id']);
				if ($phpbb3_username != null) {
					$fake_password = password_generate();
					$user_email = "revoke_".uniqid()."@localhost";
					$pwhash = password_hash($fake_password, PASSWORD_DEFAULT);

					$phpbb3_client->user_update($phpbb3_username, $user_email, $pwhash);
					$phpbb3_client->user_sessions_del($phpbb3_username);
					$phpbb3_client->user_autologin_del($phpbb3_username);
					$phpbb3_client->user_deactivate($phpbb3_username);
					$db_client->phpbb3_delete($user['id']);
				}
			}

			$db_client->usergroups_deleteby_user($user['id']);
			$db_client->character_info_unset_alliance($character_id);
			$db_client->character_info_unset_corp($character_id);
			$db_client->user_deactivate($character_id);
		}

		unset(
			$character_esi,
			$character_cache,
			$alliance_esi_id,
			$alliance_cached_id,
			$corp_esi_id,
			$corp_cached_id,
			$user_email,
			$pwhash
		);
		usleep(500000);
	}
	usleep(10000000);
}
unset($esi_client, $ts_client, $phpbb3_client);
?>