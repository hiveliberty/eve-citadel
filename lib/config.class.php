<?php

class ConfigDBWorker {
	function __construct() {
		$this->db = new SQLite3('./config/config.sqlite3');
		$this->db->exec("CREATE TABLE IF NOT EXISTS `config` (`config_section` VARCHAR (32), `config_name` VARCHAR (32), `config_value` VARCHAR (64));");
	}

    function __destruct() {
        $this->db->close();
    }

	function db_config_set_default($cfg = Null) {
		if ($cfg == Null) {
			$cfg = array();
			$cfg['host'] = 'localhost';
			$cfg['port'] = '3306';
			$cfg['database'] = 'citadel';
			$cfg['user'] = 'citadel';
			$cfg['password'] = 'password';
		}
		$this->db->exec("INSERT INTO `config` (config_section, config_name, config_value) VALUES ('db', 'host', '".$cfg['host']."');");
		$this->db->exec("INSERT INTO `config` (config_section, config_name, config_value) VALUES ('db', 'port', '".$cfg['port']."');");
		$this->db->exec("INSERT INTO `config` (config_section, config_name, config_value) VALUES ('db', 'database', '".$cfg['database']."');");
		$this->db->exec("INSERT INTO `config` (config_section, config_name, config_value) VALUES ('db', 'user', '".$cfg['user']."');");
		$this->db->exec("INSERT INTO `config` (config_section, config_name, config_value) VALUES ('db', 'password', '".$cfg['password']."');");
	}

	function db_config_update($cfg) {
		foreach ($cfg as $cfg_key => $cfg_value) {
			$statement = $this->db->prepare("UPDATE `config` SET config_value = :value WHERE config_section = 'db' AND config_name = :name;");
			$statement->bindValue(':value', $cfg_value);
			$statement->bindValue(':name', $cfg_key);
			$statement->execute();
			$statement->reset();
		}
	}

	function db_config_get() {
		$data = array();
		$result = $this->db->query("SELECT config_name, config_value FROM `config` WHERE config_section = 'db';");
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$data[$row['config_name']] = $row['config_value'];
		}
		return $data;
	}
}