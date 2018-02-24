<?php
function db_conn() {
	$db_config = require __DIR__ . '/../config/db.php';
    $conn = new mysqli($db_config['url'], $db_config['user'], $db_config['pass'], $db_config['dbname']);
	return $conn;
}

// Functions for work with `citadel_users`
function citadeldb_users_add($character_id) {
    $conn = db_conn();
    $sql = "INSERT INTO `citadel_users` SET character_id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_users_set_admin($character_id) {
    $conn = db_conn();
	$sql = "UPDATE `citadel_users` SET is_admin = 1 WHERE character_id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_users_unset_admin($character_id) {
    $conn = db_conn();
	$sql = "UPDATE `citadel_users` SET is_admin = 0 WHERE character_id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_users_set_active($character_id) {
    $conn = db_conn();
	$sql = "UPDATE `citadel_users` SET is_active = 1 WHERE character_id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_users_unset_active($character_id) {
    $conn = db_conn();
	$sql = "UPDATE `citadel_users` SET is_active = 0 WHERE character_id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_users_select($character_id) {
    $conn = db_conn();
    $sql = "SELECT id,character_id,is_active,is_admin FROM `citadel_users` WHERE character_id = '$character_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['id'])) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_users_getall() {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_users`;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_users_getall_active() {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_users` WHERE is_active = 1;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_users_select_id($id) {
    $conn = db_conn();
    $sql = "SELECT character_id FROM `citadel_users` WHERE id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['character_id'])) {
		return $result['character_id'];
	} else {
		return null;
	}
}

function citadeldb_users_admincheck($id) {
    $conn = db_conn();
    $sql = "SELECT is_admin FROM `citadel_users` WHERE id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['is_admin']) && $result['is_admin'] == 1) {
		return true;
	} else {
		return false;
	}
}

function citadeldb_users_check($character_id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_users` WHERE character_id = '$character_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['id'])) {
		return true;
	} else {
		return false;
	}
}


// Functions for work with `citadel_session_keys`
function citadeldb_session_add($user_id, $session_key, $expire_date) {
    $conn = db_conn();
    $sql = "INSERT INTO `citadel_session_keys` (user_id, session_key, expire_date) VALUES ('$user_id', '$session_key', '$expire_date');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_session_update($user_id, $session_key) {
    $conn = db_conn();
    $sql = "UPDATE `citadel_session_keys` SET session_key = '$session_key' WHERE user_id = '$user_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_session_delete($key) {
    $conn = db_conn();
    $sql = "DELETE FROM `citadel_session_keys` WHERE session_key = '$key';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_session_get($id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_session_keys` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_session_getall() {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_session_keys`;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_session_get_id($key) {
    $conn = db_conn();
    $sql = "SELECT user_id FROM `citadel_session_keys` WHERE session_key = '$key' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['user_id'])) {
		return $result['user_id'];
	} else {
		return null;
	}
}

function citadeldb_session_get_key($id) {
    $conn = db_conn();
    $sql = "SELECT session_key FROM `citadel_session_keys` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['session_key'])) {
		return $result['session_key'];
	} else {
		return null;
	}
}

function citadeldb_session_keycheck($key) {
    $conn = db_conn();
    $sql = "SELECT user_id,session_key FROM `citadel_session_keys` WHERE session_key = '$key' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['user_id'])) {
		return true;
	} else {
		return false;
	}
}

// Functions for work with `eve_alliance_info`
function citadeldb_alliance_info_add($alliance_id, $name, $ticker) {
    $conn = db_conn();
	$name = $conn->real_escape_string($name);
	$ticker = $conn->real_escape_string($ticker);
    $sql = "INSERT INTO `eve_alliance_info` (id, name, ticker) VALUES ('$alliance_id', '$name', '$ticker');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_alliance_info_get($alliance_id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_alliance_info` WHERE id = '$alliance_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_alliance_info_getall() {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_alliance_info`;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_alliance_info_getblue_ids() {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_alliance_info` WHERE blue = 1;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		$alliance_ids = array();
		foreach ($result as $alliance) {
			//if ($alliance['blue'] == 1) {
				$alliance_ids[] = $alliance['id'];
			//}
		}
		return $alliance_ids;
	} else {
		return null;
	}
}

function citadeldb_alliance_info_set_blue($alliance_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_alliance_info` SET blue = '1' WHERE id = '$alliance_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_alliance_info_unset_blue($alliance_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_alliance_info` SET blue = '0' WHERE id = '$alliance_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}


// Functions for work with `eve_corporation_info`
function citadeldb_corporation_info_add($corporation_id, $name, $ticker) {
    $conn = db_conn();
	$name = $conn->real_escape_string($name);
	$ticker = $conn->real_escape_string($ticker);
    $sql = "INSERT INTO `eve_corporation_info` (id, name, ticker) VALUES ('$corporation_id', '$name', '$ticker');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		printf("Errormessage: %s\n", $conn->error);
		$conn->close();
        return null;
    }
}

function citadeldb_corporation_info_get($corporation_id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_corporation_info` WHERE id = '$corporation_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_corporation_info_getall() {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_corporation_info`;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_corporation_info_getblue_ids() {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_corporation_info`;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		$corporation_ids = array();
		foreach ($result as $corporation) {
			if ($corporation['blue'] == 1) {
				$corporation_ids[] = $corporation['id'];
			}
		}
		return $corporation_ids;
	} else {
		return null;
	}
}

function citadeldb_corporation_info_get_alliance($alliance_id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_corporation_info` WHERE alliance_id = '$alliance_id';";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_corporation_info_getalliance_ids($alliance_id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_corporation_info` WHERE alliance_id = '$alliance_id';";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		$corporation_ids = array();
		foreach ($result as $corporation) {
			$corporation_ids[] = $corporation['id'];
		}
		return $corporation_ids;
	} else {
		return null;
	}
}

function citadeldb_corporation_info_set_alliance($corporation_id, $alliance_id) {
    $conn = db_conn();
	if ($alliance_id == 1) {
		$alliance_id = NULL;
	}
    $sql = "UPDATE `eve_corporation_info` SET alliance_id = '$alliance_id' WHERE id = '$corporation_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_corporation_info_unset_alliance($corporation_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_corporation_info` SET alliance_id = 'NULL' WHERE id = '$corporation_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_corporation_info_set_blue($corporation_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_corporation_info` SET blue = '1' WHERE id = '$corporation_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_corporation_info_unset_blue($corporation_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_corporation_info` SET blue = '0' WHERE id = '$corporation_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}


// Functions for work with `eve_character_info`
function citadeldb_character_info_add($character_id, $name) {
    $conn = db_conn();
	$name = $conn->real_escape_string($name);
    $sql = "INSERT INTO `eve_character_info` (id, name) VALUES ('$character_id', '$name');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_character_info_addfull($character_id, $name, $corporation_id, $alliance_id) {
    $conn = db_conn();
    $sql = "INSERT INTO `eve_character_info` (id, name, corporation_id, alliance_id) VALUES ('$character_id', '$name', '$corporation_id', '$alliance_id');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_character_info_get($character_id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_character_info` WHERE id = '$character_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_character_info_getall() {
    $conn = db_conn();
    $sql = "SELECT * FROM `eve_character_info`;";
    $result = $conn->query($sql)->fetch_all($resulttype=MYSQLI_ASSOC);
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_character_info_set_corp($character_id, $corporation_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_character_info` SET corporation_id = '$corporation_id' WHERE id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_character_info_unset_corp($character_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_character_info` SET corporation_id = 'NULL' WHERE id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_character_info_set_alliance($character_id, $alliance_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_character_info` SET alliance_id = '$alliance_id' WHERE id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_character_info_unset_alliance($character_id) {
    $conn = db_conn();
    $sql = "UPDATE `eve_character_info` SET alliance_id = 'NULL' WHERE id = '$character_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}


// Functions for work with `esi_tokens`
function citadeldb_token_add($character_id, $access_token, $refresh_token, $scope, $expire_date) {
    $conn = db_conn();
    $sql = "INSERT INTO `esi_tokens` (character_id, access_token, refresh_token, scope_name, expire_date)
			VALUES ('$character_id', '$access_token', '$refresh_token', '$scope', '$expire_date');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_token_update($character_id, $access_token, $scope, $expire_date) {
    $conn = db_conn();
	$sql = "UPDATE `esi_tokens` SET access_token = '$access_token', expire_date = '$expire_date'
			WHERE character_id = '$character_id' AND scope_name = '$scope';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_token_updatefull($character_id, $access_token, $refresh_token, $scope, $expire_date) {
    $conn = db_conn();
	$sql = "UPDATE `esi_tokens` SET access_token = '$access_token', refresh_token = '$refresh_token', expire_date = '$expire_date'
			WHERE character_id = '$character_id' AND scope_name = '$scope';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_token_get($character_id, $scope) {
    $conn = db_conn();
	$sql = "SELECT access_token, refresh_token, expire_date FROM `esi_tokens`
			WHERE character_id = '$character_id' AND scope_name = '$scope' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['access_token']) && isset($result['refresh_token'])) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_token_del($character_id, $scope) {
    $conn = db_conn();
	$sql = "DELETE FROM `esi_tokens` WHERE character_id = '$character_id' AND scope_name = '$scope';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

// Functions for work with `custom_storage`
function citadeldb_custom_add($custom_key, $custom_value) {
    $conn = db_conn();
    $sql = "INSERT INTO `custom_storage` (custom_key, custom_value) VALUES ('$custom_key', '$custom_value');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_custom_update($custom_key, $custom_value) {
    $conn = db_conn();
	$sql = "UPDATE `custom_storage` SET custom_value = '$custom_value' WHERE custom_key = '$custom_key';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_custom_get($custom_key) {
    $conn = db_conn();
	$sql = "SELECT custom_value FROM `custom_storage` WHERE custom_key = '$custom_key' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['custom_value'])) {
		return $result['custom_value'];
	} else {
		return null;
	}
}

function citadeldb_custom_del($custom_key) {
    $conn = db_conn();
	$sql = "DELETE FROM `custom_storage` WHERE custom_key = '$custom_key';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

// DiscordDB Functions
function discord_users_add($user_id, $discord_id) {
    $conn = db_conn();
	$sql = "INSERT INTO `discord_users` (user_id, discord_id) VALUES ('$user_id', '$discord_id');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function discord_users_select($user_id) {
    $conn = db_conn();
    $sql = "SELECT discord_id FROM `discord_users` WHERE user_id = '$user_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['discord_id'])) {
		return $result['discord_id'];
	} else {
		return null;
	}
}

function discord_users_delete($user_id) {
    $conn = db_conn();
	$sql = "DELETE FROM `discord_users` WHERE user_id = '$user_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}


// TeamSpeakDB Functions
function teamspeak_users_add($user_id, $token) {
    $conn = db_conn();
	$sql = "INSERT INTO `teamspeak_users` (user_id, token) VALUES ('$user_id', '$token');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function teamspeak_users_select($user_id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `teamspeak_users` WHERE user_id = '$user_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['token'])) {
		return $result;
	} else {
		return null;
	}
}

function teamspeak_users_delete($user_id) {
    $conn = db_conn();
	$sql = "DELETE FROM `teamspeak_users` WHERE user_id = '$user_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}