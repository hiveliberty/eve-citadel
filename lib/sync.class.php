<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

require_once(__DIR__ . '/../lib/other.php');
require_once(__DIR__ . '/../lib/db.class.php');
require_once(__DIR__ . '/../lib/auth.class.php');
require_once(__DIR__ . '/../lib/discord.class.php');
require_once(__DIR__ . '/../lib/esi.class.php');
require_once(__DIR__ . '/../lib/ts3.class.php');
require_once(__DIR__ . '/../lib/phpbb3.class.php');


class SyncManager {
	
	function __construct() {
		$this->db = new citadelDB();
		$this->config = require __DIR__ . '/../config/app.php';
		if ($this->config['services']['ts3_enabled']) {
			$this->ts = new ts3client();
		}
		if ($this->config['services']['phpbb3_enabled']) {
			$this->phpbb3 = new phpBB3client();
		}
		if ($this->config['services']['discord_enabled']) {
			$this->discord = new DiscordCitadelClient();
		}
	}

    function __destruct() {
		unset(
			$this->db,
			$this->ts,
			$this->phpbb3,
			$this->discord,
			$this->config
		);
    }

	private function sync_groups_teamspeak($group, $roles) {

		if ($group['teamspeak_enabled']) {
			if (!array_key_exists($group['name'], $roles)) {
				$this->ts->group_add($group['name']);
			}
		} else {
			if (array_key_exists($group['name'], $roles)) {
				$this->ts->group_del($roles[$group['name']]);
			}
		}

		return null;
	}

	private function sync_groups_discord($group, $roles) {
		$role_names = $this->discord->role_names();

		if ($group['discord_enabled']) {
			if (!in_array($group['name'],$role_names)) {
				$this->discord->role_add($group['name'], $group['color'], $group['discord_hoist']);
			} else {
				$this->discord->role_check_hoist($group['name'], $group['discord_hoist']);
				$this->discord->role_check_color($group['name'], $group['color']);
			}
		} else {
			foreach ($roles as $role) {
				if ($role->name == $group['name']){
					$this->discord->role_del($role->id);
				}
			}
		}

		return null;
	}

	private function sync_groups_phpbb3($group, $roles) {
		$group_name = $this->phpbb3->sanitize_groupname($group['name']);
		$group_color = $this->phpbb3->group_color($group['color']);

		if ($group['phpbb3_enabled']) {
			if (!array_key_exists($group_name, $roles)) {
				$this->phpbb3->group_add($group_name, $group_name, $group_color);
			} else {
				$this->phpbb3->group_check_color($group_name, $group_color);
			}
		} else {
			if (array_key_exists($group_name, $roles)) {
				$this->phpbb3->group_del($roles[$group_name]);
			}
		}

		return null;
	}

	private function sync_user_teamspeak($user_groups, $service_roles, $citadel_groups, $user) {
		$cldbid = $this->ts->user_get_id($user['character_id']);

		if (isset($cldbid) && $cldbid != null) {
			$ts_user_roles = $this->ts->user_get_grouplist($cldbid);

			if ($user_groups == null) {
				foreach ($citadel_groups as $citadel_group) {
					if (isset($service_roles[$citadel_group['name']])) {
						if (array_key_exists($citadel_group['name'],$ts_user_roles)) {
							$this->ts->usergroup_del($cldbid, $service_roles[$citadel_group['name']]);
						}
					}
				}
			} else {
				foreach ($citadel_groups as $citadel_group) {
					if (array_key_exists($citadel_group['name'],$ts_user_roles)) {
						if (!in_array($citadel_group['id'],$user_groups)) {
							$this->ts->usergroup_del($cldbid, $service_roles[$citadel_group['name']]);
						}
					}
				}

				foreach ($user_groups as $user_group) {
					$group = $this->db->groups_getby_id($user_group);
					if ($group['teamspeak_enabled']) {
						if (!array_key_exists($group['name'],$ts_user_roles)) {
							$status = $this->ts->usergroup_add($cldbid, $service_roles[$group['name']]);
						}
					} else {
						if (array_key_exists($group['name'],$ts_user_roles)) {
							$this->ts->usergroup_del($cldbid, $service_roles[$group['name']]);
						}
					}
				}
			}
		}
	}

	private function sync_user_discord($user_groups, $service_roles, $citadel_groups, $user) {
		$discord_id = $this->db->discord_get_id($user['id']);

		if (isset($discord_id) && $discord_id != null) {
			$discord_user_roles = $this->discord->user_role_getall($discord_id);
			$discord_role_names = $this->discord->make_key_name();

			if ($user_groups == null) {
				foreach ($citadel_groups as $citadel_group) {
					if (isset($discord_role_names[$citadel_group['name']])) {
						if (in_array($discord_role_names[$citadel_group['name']],$discord_user_roles)) {
							$this->discord->user_role_del($discord_id, $discord_role_names[$citadel_group['name']]);
							usleep(5000000);
						}
					}
				}
			} else {
				foreach ($citadel_groups as $citadel_group) {
					if (isset($discord_role_names[$citadel_group['name']])) {
						if (in_array($discord_role_names[$citadel_group['name']],$discord_user_roles)) {
							if (!in_array($citadel_group['id'],$user_groups)) {
								$this->discord->user_role_del($discord_id, $discord_role_names[$citadel_group['name']]);
								usleep(5000000);
							}
						}
					}
				}

				foreach ($user_groups as $user_group) {
					$group = $this->db->groups_getby_id($user_group);
					if ($group['discord_enabled']) {
						if (!in_array($discord_role_names[$group['name']],$discord_user_roles)) {
							$this->discord->user_role_add($discord_id, $discord_role_names[$group['name']]);
							usleep(5000000);
						}
					} else {
						// вызывало undefined variable, когда группа есть, но не дискордовская
						if (isset($discord_role_names[$group['name']])) {
							if (in_array($discord_role_names[$group['name']],$discord_user_roles)) {
								$this->discord->user_role_del($discord_id, $discord_role_names[$group['name']]);
								usleep(5000000);
							}
						}
					}
				}
			}
		}
	}

	private function sync_user_phpbb3($user_groups, $service_roles, $citadel_groups, $user) {
		$phpbb3_username = $this->db->phpbb3_get_username($user['id']);
		if (isset($phpbb3_username) && $phpbb3_username != null) {
			$phpbb3_user_roles = $this->phpbb3->user_group_getall($phpbb3_username);

			if ($user_groups == null) {
				foreach ($citadel_groups as $citadel_group) {
					if (isset($service_roles[$citadel_group['name']])) {
						if (array_key_exists($citadel_group['name'],$phpbb3_user_roles)) {
							print_r("phpBB3: Delete user {$user['id']} from group {$citadel_group['name']}.\n");
							$this->phpbb3->user_group_del($phpbb3_username, $service_roles[$citadel_group['name']]);
						}
					}
				}
			} else {
				foreach ($citadel_groups as $citadel_group) {
					if (isset($service_roles[$citadel_group['name']])) {
						//if (in_array($phpbb3_roles[$citadel_group['name']],$phpbb3_user_roles)) {
						if (is_array($phpbb3_user_roles)) {
							if (in_array($citadel_group['name'],$phpbb3_user_roles)) {
								if (!in_array($citadel_group['id'],$user_groups)) {
									print_r("phpBB3: Delete user {$user['id']} from group {$citadel_group['name']}.\n");
									$this->phpbb3->user_group_del($phpbb3_username, $service_roles[$citadel_group['name']]);
								}
							}
						}
					}
				}

				foreach ($user_groups as $user_group) {
					$group = $this->db->groups_getby_id($user_group);
					if ($group['phpbb3_enabled']) {
						if (is_array($phpbb3_user_roles)) {
							if (!in_array($group['name'],$phpbb3_user_roles)) {
							//if (!in_array($service_roles[$server_group['name']],$phpbb3_user_roles)) {
								print_r("phpBB3: Add user {$user['id']} to group {$group['name']}.\n");
								$this->phpbb3->user_group_add($phpbb3_username, $service_roles[$group['name']], 0);
							}
						} else {
							print_r("phpBB3: Add user {$user['id']} to group {$group['name']}.\n");
							$this->phpbb3->user_group_add($phpbb3_username, $service_roles[$group['name']], 0);
						}
					} else {
						if (in_array($group['name'],$phpbb3_user_roles)) {
						//if (in_array($service_roles[$server_group['name']],$phpbb3_user_roles)) {
							print_r("phpBB3: Delete user {$user['id']} from group {$group['name']}.\n");
							$this->phpbb3->user_group_del($phpbb3_username, $service_roles[$group['name']]);
						}
					}
				}
			}

			$this->phpbb3->user_permissions_clear($phpbb3_username);
		}
	}

	function corp_groups() {
		$config = $this->config['auth'];
		$alliance_id = $this->db->custom_get("member_id");
		$corporations = $this->db->corporation_info_get_alliance($alliance_id);

		foreach ($corporations as $corporation) {
			$group_name = corp_group_name($corporation['ticker']);
			if ($config['set_corp_role']) {
				if ($this->db->groups_getby_name($group_name) == null) {
					$this->db->authgroups_add($group_name, 1, $config['corp_color'], $config['corp_hoist']);
				} else {
					$this->db->groups_service_enable($group_name);
					$this->db->groups_update($group_name, 1, $config['corp_color'], $config['corp_hoist']);
				}
			} else {
				$this->db->groups_service_disable($group_name);
			}
		}
	}

	function server_groups() {
		$config = $this->config['services'];
		$groups = $this->db->groups_getall();
		if ($config['ts3_enabled']) {
			$ts_roles = $this->ts->group_list_get();
		}
		if ($config['discord_enabled']) {
			$discord_roles = $this->discord->guild_roles_get();
		}
		if ($config['phpbb3_enabled']) {
			$phpbb3_roles = $this->phpbb3->group_getall();
		}

		foreach ($groups as $group) {
			if ($config['ts3_enabled']) {
				$this->sync_groups_teamspeak($group, $ts_roles);
			}
			if ($config['phpbb3_enabled']) {
				$this->sync_groups_phpbb3($group, $phpbb3_roles);
			}
			if ($config['discord_enabled']) {
				$this->sync_groups_discord($group, $discord_roles);
				usleep(1000000);
			}
		}
	}

	function user_groups() {
		$config = $this->config['services'];
		$users = $this->db->users_get_active();
		$citadel_groups = $this->db->groups_getall();
		if ($config['discord_enabled']) {
			$this->discord->guild_roles();
			$discord_roles = $this->discord->guild_roles_get();
		}
		if ($config['ts3_enabled']) {
			$ts_roles = $this->ts->group_list_get();
		}
		if ($config['phpbb3_enabled']) {
			$phpbb3_roles = $this->phpbb3->group_getall();
		}

		foreach(array_chunk($users, 5, true) as $users_chunk) {
			foreach ($users_chunk as $user) {
				$user_groups = $this->db->usergroups_getby_user($user['id']);

				if ($config['ts3_enabled']) {
					$this->sync_user_teamspeak($user_groups, $ts_roles, $citadel_groups, $user);
				}
				if ($config['phpbb3_enabled']) {
					$this->sync_user_phpbb3($user_groups, $phpbb3_roles, $citadel_groups, $user);
				}
				if ($config['discord_enabled']) {
					$this->sync_user_discord($user_groups, $discord_roles, $citadel_groups, $user);
					usleep(1000000);
				}
			}
			usleep(5000000);
		}
	}
}