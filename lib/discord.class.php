<?php
// Discord lib
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
use RestCord\DiscordClient;

require_once(__DIR__ . '/../lib/other.php');

class DiscordCitadelClient {

	function __construct() {
		$this->logger = get_logger("discord", "NOTICE", false);

		$this->config = require __DIR__ . '/../config/discord.php';
		$this->client = new DiscordClient([
			'token' => $this->config['token'],
			'logger' => $this->logger
		]);
		$this->roles = $this->client->guild->getGuildRoles([
			'guild.id' => (int)$this->config['guild_id']
		]);
	}

	function role_names() {
		$new_array = array();
		foreach ($this->roles as $role) {
			$new_array[] = $role->name;
		}
		return $new_array;
	}

	function make_key_name() {
		$new_array = array();
		foreach ($this->roles as $role) {
			$new_array[$role->name] = $role->id;
		}
		return $new_array;
	}

	function make_key_id() {
		$new_array = array();
		foreach ($this->roles as $role) {
			$new_array[$role->id] = $role->name;
		}
		return $new_array;
	}

	function role_exist($role_name) {
		foreach ($this->roles as $role) {
			if ($role->name == $role_name) {
				return true;
			}
		}
		return false;
	}

	function user_exist($id) {
		
		if ($this->user_get($id) != null) {
			return true;
		}
		return false;
	}

	function get_role($role_name) {
		foreach ($this->roles as $role) {
			if ($role->name == $role_name) {
				return $role;
			}
		}
		return null;
	}

	function role_check_color($role_name, $color) {
		foreach ($this->roles as $role) {
			if ($role->name == $role_name) {
				if ($role->color != $color) {
					$this->client->guild->modifyGuildRole([
						'guild.id' => (int)$this->config['guild_id'],
						'role.id' => $role->id,
						'color' => (int)$color
					]);
				}
			}
		}
	}

	function role_check_hoist($role_name, $hoist) {
		foreach ($this->roles as $role) {
			if ($role->name == $role_name) {
				if ($role->hoist != $hoist) {
					$this->client->guild->modifyGuildRole([
						'guild.id' => (int)$this->config['guild_id'],
						'role.id' => $role->id,
						'hoist' => (bool)$hoist
					]);
				}
			}
		}
	}

	function sanitize_name($nick) {
		$nick = substr($nick, 0, 32);
		return $nick;
	}

	public function guild_get() {
		$guild = $this->client->guild->getGuild([
			'guild.id' => (int)$this->config['guild_id']
		]);
		return $guild;
	}

	public function guild_roles() {
		$this->roles = $this->client->guild->getGuildRoles([
			'guild.id' => (int)$this->config['guild_id']
		]);
	}

	public function guild_roles_get() {
		return $this->roles;
	}

	public function guild_role_get($role_name) {
		return $this->get_role($role_name);
	}

	public function role_add($role_name, $role_color = 0, $role_hoist = false) {
		$role = $this->client->guild->createGuildRole([
			'guild.id' => (int)$this->config['guild_id'],
			'name' => $role_name,
			'hoist' => (bool)$role_hoist,
			'color' => (int)$role_color
		]);
		return $role;
	}

	public function role_del($role_id) {
		$response = $this->client->guild->deleteGuildRole([
			'guild.id' => (int)$this->config['guild_id'],
			'role.id' => $role_id
		]);
		return $response;
	}

	public function guild_roles_key_name() {
		$roles = $this->guild_roles();
		$roles = $this->make_key_name($roles);
		return $roles;
	}

	public function user_add($discord_id, $token, $nick = null, $roles = null) {
		try {
			$response = $this->client->guild->addGuildMember([
				'guild.id' => (int)$this->config['guild_id'],
				'user.id' => (int)$discord_id,
				'access_token' => (string)$token,
				'nick' => (string)$this->sanitize_name($nick),
				'roles' => $roles
			]);
			return $response;
		} catch(Exception $e) {
			$this->logger->error($e);
			return;
		}
	}

	public function user_get($discord_id) {
		try {
			$response = $this->client->guild->getGuildMember([
				'guild.id' => (int)$this->config['guild_id'],
				'user.id' => (int)$discord_id
			]);
			return $response;
		} catch(Exception $e) {
			$this->logger->error($e);
			return;
		}
	}

	public function user_del($discord_id) {
		try {
			$response = $this->client->guild->removeGuildMember([
				'guild.id' => (int)$this->config['guild_id'],
				'user.id' => (int)$discord_id
			]);
			return $response;
		} catch(Exception $e) {
			$this->logger->error($e);
			return;
		}
	}

	public function user_nick_set($discord_id, $discord_nick) {
		try {
			$response = $this->client->guild->modifyGuildMember([
				'guild.id' => (int)$this->config['guild_id'],
				'user.id' => (int)$discord_id,
				'nick' => $discord_nick
			]);
			return $response;
		} catch(Exception $e) {
			$this->logger->error($e);
			return null;
		}
	}

	public function user_role_getall($discord_id) {
		try {
			$guild_member = $this->client->guild->getGuildMember([
				'guild.id' => (int)$this->config['guild_id'],
				'user.id' => (int)$discord_id
			]);
			return $guild_member->roles;
		} catch(Exception $e) {
			$this->logger->error($e);
			return null;
		}
	}

	public function user_role_add($discord_id, $role_id) {
		try {
			$response = $this->client->guild->addGuildMemberRole([
				'guild.id' => (int)$this->config['guild_id'],
				'user.id' => (int)$discord_id,
				'role.id' => (int)$role_id
			]);
			return $response;
		} catch(Exception $e) {
			$this->logger->error($e);
			return null;
		}
	}

	public function user_role_del($discord_id, $role_id) {
		try {
			$response = $this->client->guild->removeGuildMemberRole([
				'guild.id' => (int)$this->config['guild_id'],
				'user.id' => (int)$discord_id,
				'role.id' => (int)$role_id
			]);
			return $response;
		} catch(Exception $e) {
			$this->logger->error($e);
			return null;
		}
	}
}
