<?php
// ESI lib

require_once(__DIR__ . '/../lib/vendor/ts3admin.class.php');

class ts3client {
	
	function __construct() {
		$config = require __DIR__ . '/../config/ts3.php';
		$this->ts_client = new ts3admin($config['url'], $config['query_port']);
		if($this->ts_client->getElement('success', $this->ts_client->connect())) {
			$this->ts_client->login($config['user'], $config['pass']);
			$this->ts_client->selectServer($config['port']);
			$this->ts_connected = true;
		} else {
			$this->ts_connected = false;
		}
	}

    function __destruct() {
        $this->ts_client->quit();
    }
	
	public function check_group($name) {
		$group = $this->ts_client->group_get_byname($name);
		if ($group == null) {
			return false;
		} else{
			return true;
		}
	}
	
	public function group_list_get() {
		try {
			if ($this->ts_connected) {
				$groups = [];
				$server_groups = $this->ts_client->serverGroupList();
				$server_groups = $server_groups['data'];

				foreach ($server_groups as $group) {
					$groups[$group['name']] = $group['sgid'];
				}

				return $groups;
			} else {
				return null;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function group_get_byname($name) {
		try {
			if($this->ts_connected) {
				$server_groups = $this->ts_client->serverGroupList();
				$server_groups = $server_groups['data'];

				foreach ($server_groups as $group) {
					if ($group['name'] == $name) {
						return $group['sgid'];
					}
				}
				return null;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function group_add($name) {
		try {
			if ($this->ts_connected) {
				$permissions = array();
				$permissions['i_group_needed_modify_power'] = array('75', '0', '0');
				$permissions['i_group_needed_member_add_power'] = array('100', '0', '0');
				$permissions['i_group_needed_member_remove_power'] = array('100', '0', '0');

				$sgid = $this->ts_client->serverGroupAdd($name);
				$sgid = $sgid['data']['sgid'];
				$this->ts_client->serverGroupAddPerm($sgid, $permissions);

				return $sgid;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function group_del($sgid) {
		try {
			if ($this->ts_connected) {
				//$this->ts_client->serverGroupDelete($sgid, $force = 1);
				$response = $this->ts_client->serverGroupDelete($sgid);
				return $response;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function user_add($char_id, $char_name, $group_id) {
		try {
			if ($this->ts_connected) {
				$custom_field = array();
				$custom_field['character_id'] = $char_id;

				$user_data = $this->ts_client->privilegekeyAdd(0, $group_id, 0, 'Auth token for '.$char_name, $custom_field);

				return $user_data['data'];
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function user_get_id($char_id) {
		try {
			if ($this->ts_connected) {
				$custom_field = $this->ts_client->customSearch('character_id', $char_id);
				$cldbid = $custom_field['data'][0]['cldbid'];
				return $cldbid;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function user_get_grouplist($cldbid) {
		try {
			if ($this->ts_connected) {
				$client_groups = $this->ts_client->serverGroupsByClientID($cldbid);
				$client_groups = $client_groups['data'];

				$groups = array();
				foreach ($client_groups as $group) {
					$groups[$group['name']] = $group['sgid'];
				}

				return $groups;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function user_del($char_id, $token) {
		try {
			if ($this->ts_connected) {
				$this->ts_client->privilegekeyDelete($token);
				$custom_field = $this->ts_client->customSearch('character_id', $char_id);
				$cldbid = $custom_field['data'][0]['cldbid'];

				$clientlist = $this->ts_client->clientList();
				$clientlist = $clientlist['data'];

				foreach ($clientlist as $client) {
					if ($client['client_database_id'] == $cldbid) {
						$this->ts_client->clientKick($client['clid'], $kickMode = "server", $kickmsg = "Auth service deleted");
					}
				}

				$this->ts_client->clientDbDelete($cldbid);

				return null;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function usergroup_add($cldbid, $sgid) {
		try {
			if ($this->ts_connected) {
				$response = $this->ts_client->serverGroupAddClient($sgid, $cldbid);
				return $response;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

	public function usergroup_del($cldbid, $sgid) {
		try {
			if ($this->ts_connected) {
				$response = $this->ts_client->serverGroupDeleteClient($sgid, $cldbid);
				return $response;
			}
		}
		catch(Exception $e) {
			return null;
		}
	}

}