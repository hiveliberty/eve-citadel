<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

$output = "[%datetime%] %channel%.%level_name%: %message%\n";
$formatter = new LineFormatter($output);
$logger = new Logger('user_check');
$log_handler_file = new StreamHandler(__DIR__ . '/../logs/user_check/user_check-'.date("Y-m-d_H-i-s", time()).'.log', Logger::INFO);
$log_handler_console = new StreamHandler('php://stdout', Logger::INFO);
$log_handler_console->setFormatter($formatter);
$log_handler_file->setFormatter($formatter);
$logger->pushHandler($log_handler_file);
$logger->pushHandler($log_handler_console);
$logger->info('Logger Initiated');

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
$esi_client = new ESIClient("tranquility", null, $logger);
if (!$esi_client->is_online()) {
	$logger->info("EVE ESI is not online");
	die();
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

$logger->info("Started users checking");

foreach(array_chunk($users, 5, true) as $users_chunk) {
	foreach ($users_chunk as $user) {
		$character_id = $user['character_id'];
		$character_cache = $db_client->character_info_get($character_id);
		$character_esi = $esi_client->character_get_details($character_id);

		if (!isset($character_esi) && $character_esi == null) {
			$logger->info("ESI did not return the correct result. Skip checking {$character_cache['name']}");
			//break 2;
			continue;
		}

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

		$logger->info("Checking user {$character_esi['name']}");
		if ($auth_manager->is_member($alliance_esi_id, $corp_esi_id)) {
			$auth_manager->character_check_membership($character_id, $character_esi, $character_cache);
			$auth_manager->auth_role_check($user['id'], true);
			$auth_manager->corp_role_check($user['id'], $group_old, $group_new, true);
		} elseif ($auth_manager->is_blue($alliance_esi_id, $corp_esi_id)) {
			$auth_manager->character_check_membership($character_id, $character_esi, $character_cache);
			$auth_manager->auth_role_check($user['id'], false);
			$auth_manager->corp_role_check($user['id'], $group_old, $group_new, false);
		} else {
			$logger->info("Now user {$character_esi['name']} is not member or blue. Delete all roles");

			if ($config['services']['ts3_enabled']) {
				$logger->info("Delete roles {$character_esi['name']} for TS3");
				$ts_token = $db_client->teamspeak_get_token($user['id']);
				if ($ts_token != null) {
					$db_client->teamspeak_delete($user['id']);
					$ts_client->user_del($character_id, $ts_token);
				}
			}

			if ($config['services']['discord_enabled']) {
				$logger->info("Delete roles {$character_esi['name']} for Discord");
				$discord_id = $db_client->discord_get_id($user['id']);
				if ($discord_id != null) {
					$discord_client->user_del($discord_id);
					$db_client->discord_delete($user['id']);
				}
			}

			if ($config['services']['phpbb3_enabled']) {
				$logger->info("Delete roles {$character_esi['name']} for phpBB3");
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
	usleep(2000000);
}
$logger->info("User checking has been completed");
unset($esi_client, $ts_client, $phpbb3_client);
?>