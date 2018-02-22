<?php
// ESI lib

require_once(__DIR__ . '/../lib/vendor/ts3admin.class.php');

$ts3_config = require __DIR__ . '/../config/ts3.php';

function TSGroupListGet()
{
	try {
		$ts3_config = require __DIR__ . '/../config/ts3.php';
		$ts3_client = new ts3admin($ts3_config['url'], $ts3_config['query_port']);
		if($ts3_client->getElement('success', $ts3_client->connect())) {
			$ts3_client->login($ts3_config['user'], $ts3_config['pass']);
			$ts3_client->selectServer($ts3_config['port']);
			$groups = [];
			$server_groups = $ts3_client->serverGroupList();
			$ts3_client->quit();
			$server_groups = $server_groups['data'];

			foreach ($server_groups as $group) {
				$groups[$group['name']] = $group['sgid'];
			}

			return $groups;
		}
    }
	catch(Exception $e) {
		return null;
    }
}

function TSGroupGetByName($name)
{
	try {
		$ts3_config = require __DIR__ . '/../config/ts3.php';
		$ts3_client = new ts3admin($ts3_config['url'], $ts3_config['query_port']);
		if($ts3_client->getElement('success', $ts3_client->connect())) {
			$ts3_client->login($ts3_config['user'], $ts3_config['pass']);
			$ts3_client->selectServer($ts3_config['port']);
			$server_groups = $ts3_client->serverGroupList();
			$ts3_client->quit();
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

//function TSGroupAdd($name, $permissions)
function TSGroupAdd($name)
{
	try {
		$ts3_config = require __DIR__ . '/../config/ts3.php';
		$ts3_client = new ts3admin($ts3_config['url'], $ts3_config['query_port']);
		if($ts3_client->getElement('success', $ts3_client->connect())) {
			$permissions = array();
			$permissions['i_group_needed_modify_power'] = array('75', '0', '0');
			$permissions['i_group_needed_member_add_power'] = array('75', '0', '0');
			$permissions['i_group_needed_member_remove_power'] = array('75', '0', '0');

			$ts3_client->login($ts3_config['user'], $ts3_config['pass']);
			$ts3_client->selectServer($ts3_config['port']);
			$sgid = $ts3_client->serverGroupAdd($name);
			$sgid = $sgid['data']['sgid'];
			$ts3_client->serverGroupAddPerm($sgid, $permissions);
			$ts3_client->quit();

			return $sgid;
		}
    }
	catch(Exception $e) {
		return null;
    }
}

function TSAddUser($char_id, $char_name, $group_id)
{
	try {
		$ts3_config = require __DIR__ . '/../config/ts3.php';
		$ts3_client = new ts3admin($ts3_config['url'], $ts3_config['query_port']);
		if($ts3_client->getElement('success', $ts3_client->connect())) {
			$customFieldSet = array();
			$customFieldSet['character_id'] = $char_id;
			
			$ts3_client->login($ts3_config['user'], $ts3_config['pass']);
			$ts3_client->selectServer($ts3_config['port']);

			$user_data = $ts3_client->privilegekeyAdd(0, $group_id, 0, 'Auth token for '.$char_name, $customFieldSet);
			$ts3_client->quit();

			return $user_data['data'];
		}
    }
	catch(Exception $e) {
		return null;
    }
}

function TSDelUser($char_id, $token)
{
	try {
		$ts3_config = require __DIR__ . '/../config/ts3.php';
		$ts3_client = new ts3admin($ts3_config['url'], $ts3_config['query_port']);
		$ts3_client->privilegekeyDelete($token);

		if($ts3_client->getElement('success', $ts3_client->connect())) {
			$ts3_client->login($ts3_config['user'], $ts3_config['pass']);
			$ts3_client->selectServer($ts3_config['port']);

			$customfield = $ts3_client->customSearch('character_id', $char_id);
			$cldbid = $customfield['data'][0]['cldbid'];

			$clientlist = $ts3_client->clientList();
			$clientlist = $clientlist['data'];

			foreach ($clientlist as $client) {
				if ($client['client_database_id'] == $cldbid) {
					$kickstatus = $ts3_client->clientKick($client['clid'], $kickMode = "server", $kickmsg = "Auth service deleted");
				}
			}

			$ts3_client->clientDbDelete($cldbid);
			$ts3_client->quit();

			return null;
		}
    }
	catch(Exception $e) {
		return null;
    }
}