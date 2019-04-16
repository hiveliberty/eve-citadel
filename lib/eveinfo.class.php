<?php

require_once(__DIR__ . '/../lib/db.class.php');
require_once(__DIR__ . '/../lib/other.php');
require_once(__DIR__ . '/../lib/token.php');
require_once(__DIR__ . '/../lib/esi.class.php');

class EveInfoManager {
	
	function __construct($db_client = null, $esi_client = null) {
		if ($db_client == null) {
			$this->db = new citadelDB();
		} else {
			$this->db = $db_client;
		}

		if ($esi_client == null) {
			$this->esi = new ESIClient();
		} else {
			$this->esi = $esi_client;
		}
	}

    function __destruct() {
		unset($this->db, $this->esi);
    }

	function member_exist() {
		$member_id = $this->db->custom_get("member_id");
		if ($member_id != NULL) {
			return true;
		} else {
			return false;
		}
	}

	function contacts_ids($contacts, $type, $min_standing) {
		$ids = array();
		foreach ($contacts as $contact) {
			if ($contact['standing'] >= $min_standing) {
				if ($contact['contact_type'] == $type) {
					$ids[] = $contact['contact_id'];
				}
			}
		}
		return $ids;
	}

	function check_alliance($alliance_id) {
		$alliance_cache = $this->db->alliance_info_get($alliance_id);

		if ($alliance_cache == null) {
			$alliance_data = $this->esi->alliance_get_details($alliance_id);
			$this->db->alliance_info_add($alliance_id, $alliance_data['name'], $alliance_data['ticker']);
		}
	}

	function check_corporation($corporation_id) {
		$corporation_cache = $this->db->corporation_info_get($corporation_id);

		if ($corporation_cache == null) {
			$corporation_data = $this->esi->corporation_get_details($corporation_id);
			$this->db->corporation_info_add($corporation_id, $corporation_data['name'], $corporation_data['ticker']);
		}
	}

	function check_alliance_corporations($alliance_id, $config) {
		$corporation_cache_ids = $this->db->corporation_info_getalliance_ids($alliance_id);
		$alliance_corporations = $this->esi->alliance_get_corporations($alliance_id);
		$member_id = $this->db->custom_get("member_id");

		if ($alliance_corporations != null) {
			if ($corporation_cache_ids != $alliance_corporations) {
				if ($corporation_cache_ids != null) {
					foreach ($corporation_cache_ids as $corporation_cache_id) {
						if (!in_array($corporation_cache_id,$alliance_corporations)) {
							$this->db->corporation_info_unset_alliance($corporation_cache_id);
							$corporation_cache = $this->db->corporation_info_get($corporation_cache_id);
							$group_name = corp_group_name($corporation_cache['ticker']);
							if ($this->db->groups_getby_name($group_name) != null) {
								$this->db->groups_service_disable($group_name);
							}
						}
					}
				}

				foreach ($alliance_corporations as $corporation_id) {
					$this->check_corporation($corporation_id);
					$this->db->corporation_info_set_alliance($corporation_id, $alliance_id);
					if ($alliance_id == $member_id) {
						$corporation_cache = $this->db->corporation_info_get($corporation_id);
						$group_name = corp_group_name($corporation_cache['ticker']);
						if ($this->db->groups_getby_name($group_name) == null) {
							$this->db->authgroups_add($group_name, 1, $config['corp_color'], $config['corp_hoist']);
						} else {
							$this->db->groups_service_enable($group_name);
							$this->db->groups_update($group_name, 1, $config['corp_color'], $config['corp_hoist']);
						}
					}
				}
			}
		}
	}

	function check_blue_alliances($contacts, $config) {
		$alliance_cache_ids = $this->db->alliance_info_getblue_ids();
		$contacts_ids = $this->contacts_ids($contacts, 'alliance', 5);

		foreach ($alliance_cache_ids as $alliance_id) {
			if (!in_array($alliance_id,$contacts_ids)) {
				$this->db->alliance_info_unset_blue($alliance_id);
			}
			$this->check_alliance_corporations($alliance_id, $config);
		}

		foreach ($contacts_ids as $alliance_id) {
			if (!in_array($alliance_id,$alliance_cache_ids)) {
				$this->check_alliance($alliance_id);
				$this->db->alliance_info_set_blue($alliance_id);
				$this->check_alliance_corporations($alliance_id, $config);
			}
		}
	}

	function check_blue_corporations($contacts) {
		$corporation_cache_ids = $this->db->corporation_info_getblue_ids();
		$contacts_ids = $this->contacts_ids($contacts, 'corporation', 5);

		foreach ($corporation_cache_ids as $corporation_id) {
			if (!in_array($corporation_id,$contacts_ids)) {
				$this->db->corporation_info_unset_blue($corporation_id);
			}
		}

		foreach ($contacts_ids as $corporation_id) {
			if (!in_array($corporation_id,$corporation_cache_ids)) {
				$this->check_corporation($corporation_id);
				$this->db->corporation_info_set_blue($corporation_id);
			}
		}
	}
}
