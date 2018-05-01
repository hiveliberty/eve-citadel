<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/lib/token.php');
require_once(__DIR__ . '/lib/db.class.php');
require_once(__DIR__ . '/lib/other.php');
require_once(__DIR__ . '/lib/sync.class.php');
require_once(__DIR__ . '/lib/esi.class.php');
require_once(__DIR__ . '/lib/eveinfo.class.php');
require_once(__DIR__ . '/lib/phpbb3.class.php');
require_once(__DIR__ . '/lib/discord.class.php');

$config = require __DIR__ . '/config/app.php';

if (isset($argv[1])) {
	$db_client = new citadelDB();

	switch ($argv[1]) {
		case 'owner':
			switch ($argv[2]) {
				case 'add':
					$member_id = $db_client->custom_get("member_id");
					if ($member_id == null) {
						$db_client->custom_add("member_id", $argv[3]);
					} else {
						$db_client->custom_update("member_id", $argv[3]);
					}
					print_r("Added {$argv[3]} as owner.\n");

					$eveinfo_manager = new EveInfoManager($db_client);
					$eveinfo_manager->check_alliance($member_id);
					print_r("Info for ID {$argv[3]} is loaded to cache.\n");

					unset($db_client, $eveinfo_manager);
					break;
				case 'check':
					$member_id = $db_client->custom_get("member_id");
					if ($member_id == null) {
						print_r("No owner is set.\n");
					} else {
						print_r("Owner is {$member_id}.\n");
					}
					unset($db_client);
					break;
			}
			break;

		case 'groups':
			switch ($argv[2]) {

				case 'add':
					if ($db_client->groups_add($argv[3])) {
						$group = $db_client->groups_getby_name($argv[3]);
						print_r("Group '{$argv[3]}' added with group_id '{$group['id']}'.\n");
					} else {
						print_r("Cann't add group '{$argv[3]}' to database.\n");
					}
					unset($db_client, $sync);
					break;

				case 'del':
					$group = $db_client->groups_getby_name($argv[3]);
					if ($group != null) {
						print_r("Trying to delete a group '{$argv[3]}'.\n");
						$db_client->groups_deleteby_name($argv[3]);

						$group = $db_client->groups_getby_name($argv[3]);
						if ($group == null) {
							print_r("Group '{$argv[3]}' successfully deleted from database.\n");
						} else {
							print_r("Cann't delete '{$argv[3]}' group from database.\n");
						}
					} else {
						print_r("Group '{$argv[3]}' does not exist.\n");
					}
					unset($db_client, $sync);
					break;

				case 'disable_all':
					$sync = new SyncManager();
					$db_client->groups_disable_all();
					$sync->server_groups();
					unset($db_client, $sync);
					break;

				case 'init':
					$sync = new SyncManager();
					if ($db_client->groups_getby_name($config['auth']['role_member']) == null) {
						$db_client->authgroups_add($config['auth']['role_member'], 1, $config['auth']['member_color']);
					}
					if ($db_client->groups_getby_name($config['auth']['role_blue']) == null) {
						$db_client->authgroups_add($config['auth']['role_blue'], 1, $config['auth']['blue_color']);
					}
					$member_id = $db_client->custom_get("member_id");
					if ($member_id == null) {
						print_r("No member is set. Corporation does not synchronized.\n");
					} else {
						$sync->corp_groups();
					}
					$sync->server_groups();
					unset($db_client, $sync);
					break;

				case 'list':
					$groups = $db_client->groups_getall();
					if ($groups == null) {
						print_r("Not found any groups in database.\n");
					} else {
						$group_names = make_arrayby_key($groups, "name");
						foreach ($group_names as $group_name) {
							print_r("{$group_name}\n");
						}
					}
					unset($db_client, $sync);
					break;

				case 'sync':
					$sync = new SyncManager();
					$sync->corp_groups();
					$sync->server_groups();
					$sync->user_groups();
					unset($db_client, $sync);
					break;

				default:
					die("Unknown command.\n");
					break;

			}
			break;

		default:
			die("Unknown command.\n");
			break;
	}
}

?>