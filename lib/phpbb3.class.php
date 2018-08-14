<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class phpBB3client {
	
	function __construct() {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$config = require __DIR__ . '/../config/db.php';
		$this->db = new mysqli($config['url'], $config['user'], $config['pass'], $config['db']['phpbb3']);
		
		$this->logger = new Logger('phpBB3client');
		$this->logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/other.log', Logger::WARNING));
	}

    function __destruct() {
        $this->db->close();
    }

	public function sanitize_username($username) {
		$username = mb_strtolower($username, 'UTF-8');
		$username = str_replace("'", '_', $username);
		$username = preg_replace('/[^a-z0-9 -_]+/', '', $username);
		$username = str_replace(' ', '_', $username);
		return $username;
	}

	public function sanitize_groupname($groupname) {
		$groupname = preg_replace('/[^a-zA-Z0-9 -.]+/', '', $groupname);
		//$group_name = str_replace(' ', '_', $group_name);
		return $groupname;
	}

	public function group_color($color) {
		if ($color == 0) {
			return null;
		}
		$color = dechex($color);
		$color = mb_strtoupper($color, 'UTF-8');
		return $color;
	}

	public function check_user($username) {
		$user_id = $this->user_get_id($username);
		if ($user_id == null) {
			return false;
		} else {
			return true;
		}
	}

	public function check_group($groupname) {
		$group_id = $this->group_get_id($groupname);
		if ($group_id == null) {
			return false;
		} else {
			return true;
		}
	}

	public function user_add($username, $username_clean, $user_password, $user_email, $group_id, $user_regdate, $user_permissions = "", $user_sig = "") {
		try {
			$username = $this->db->real_escape_string($username);
			$sql = "INSERT INTO `phpbb_users`
				(username, username_clean, user_password, user_email, group_id, user_regdate, user_permissions, user_sig, user_lang, user_style)
				VALUES ('$username', '$username_clean', '$user_password', '$user_email', '$group_id', '$user_regdate', '$user_permissions', '$user_sig', 'en', '1')";
			if ($this->db->query($sql) === TRUE) {
				return true;
			} else {
				return false;
			}
		//} catch(mysqli_sql_exception $e) {
		} catch(Exception $e) {
			$this->logger->error($e);
		}
	}

	public function user_activate($username) {
		$sql = "UPDATE `phpbb_users` SET user_type = '0' WHERE username_clean = '$username';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	public function user_deactivate($username) {
		$sql = "UPDATE `phpbb_users` SET user_type = '1' WHERE username_clean = '$username';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	public function user_password_update($username, $user_password) {
		$sql = "UPDATE `phpbb_users` SET user_password = '$user_password' WHERE username_clean = '$username';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	public function user_permissions_clear($username) {
		$user_id = $this->user_get_id($username);
		if ($user_id != null) {
			$sql = "UPDATE `phpbb_users` SET user_permissions = '' WHERE user_id = '$user_id';";
			if ($this->db->query($sql) === TRUE) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function user_update($username, $user_email, $user_password) {
		$sql = "UPDATE `phpbb_users` SET user_email = '$user_email', user_password = '$user_password' WHERE username_clean = '$username';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	public function user_avatar_set($username, $character_id) {
		$user_id = $this->user_get_id($username);
		if ($user_id != null) {
			$avatar_url = "https://image.eveonline.com/Character/".$character_id."_64.jpg";
			$sql = "UPDATE `phpbb_users` SET user_avatar_type = 2, user_avatar_width = 64, user_avatar_height = 64, user_avatar = '$avatar_url' WHERE user_id = '$user_id';";
			if ($this->db->query($sql) === TRUE) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function user_get_id($username) {
		$sql = "SELECT user_id FROM `phpbb_users` WHERE username_clean = '$username';";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['user_id'])) {
			return $result['user_id'];
		} else {
			return null;
		}
	}

	public function user_del($username) {
		$sql = "DELETE FROM `phpbb_users` WHERE username_clean = '$username';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	public function user_sessions_del($username) {
		$user_id = $this->user_get_id($username);
		if ($user_id != null) {
			$sql = "DELETE FROM `phpbb_sessions` WHERE session_user_id = '$user_id';";
			if ($this->db->query($sql) === TRUE) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function user_autologin_del($username) {
		$user_id = $this->user_get_id($username);
		if ($user_id != null) {
			$sql = "DELETE FROM `phpbb_sessions_keys` WHERE user_id = '$user_id';";
			if ($this->db->query($sql) === TRUE) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function user_group_add($username, $group_id, $user_pending) {
		$user_id = $this->user_get_id($username);
		if ($user_id != null) {
			$sql = "INSERT INTO `phpbb_user_group` (group_id, user_id, user_pending) VALUES ('$group_id', '$user_id', '$user_pending');";
			if ($this->db->query($sql) === TRUE) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	//public function user_group_getall($user_id) {
	public function user_group_getall($username) {
		$user_id = $this->user_get_id($username);
		if ($user_id != null) {
			$sql = "SELECT phpbb_groups.group_name
					FROM `phpbb_groups`, `phpbb_user_group`
					WHERE phpbb_user_group.group_id = phpbb_groups.group_id AND user_id= '$user_id';";
			$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
			if (isset($result)) {
				$groups = array();
				foreach ($result as $group) {
					$groups[] = $group['group_name'];
				}
				return $groups;
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	public function user_group_del($username, $group_id) {
		$user_id = $this->user_get_id($username);
		if ($user_id != null) {
			$sql = "DELETE FROM `phpbb_user_group` WHERE user_id = '$user_id' AND group_id = '$group_id';";
			if ($this->db->query($sql) === TRUE) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function group_add($group_name, $group_desc, $color = "") {
		$sql = "INSERT INTO `phpbb_groups` (group_name, group_desc, group_legend, group_colour) VALUES ('$group_name', '$group_desc', '0', '$color');";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	public function group_getall() {
		$sql = "SELECT group_id, group_name FROM `phpbb_groups`;";
		$result = $this->db->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
		if (isset($result)) {
			$groups = array();
			foreach ($result as $group) {
				$groups[$group['group_name']] = $group['group_id'];
			}
			return $groups;
		} else {
			return null;
		}
	}

	public function group_get_id($group_name) {
		$sql = "SELECT group_id FROM `phpbb_groups` WHERE group_name = '$group_name';";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['group_id'])) {
			return $result['group_id'];
		} else {
			return null;
		}
	}

	public function group_del($group_id) {
		$sql = "DELETE FROM `phpbb_groups` WHERE group_id = '$group_id';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

	public function group_check_color($group_name, $color) {
		$old_color = $this->group_get_color($group_name);
		if ($old_color != $color) {
			$this->group_update_color($group_name, $color);
		}
	}

	public function group_get_color($group_name) {
		$sql = "SELECT group_colour FROM `phpbb_groups` WHERE group_name = '$group_name';";
		$result = $this->db->query($sql)->fetch_assoc();
		if (isset($result['group_colour'])) {
			return $result['group_colour'];
		} else {
			return null;
		}
	}

	public function group_update_color($group_name, $color) {
		$sql = "UPDATE `phpbb_groups` SET group_colour = '$color' WHERE group_name = '$group_name';";
		if ($this->db->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}
}