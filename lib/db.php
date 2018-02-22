<?php
// Some custom lib



function db_conn() {
	$db_config = require __DIR__ . '/../config/db.php';
    $conn = new mysqli($db_config['url'], $db_config['user'], $db_config['pass'], $db_config['dbname']);
	return $conn;
}

//function citadelUsersAdd($db, $user, $pass, $dbName, $characterID)
function citadeldb_users_add($characterID) {
    $conn = db_conn();
    $sql = "INSERT INTO `citadel_users` SET character_id = '$characterID';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_users_add_admin($characterID) {
    $conn = db_conn();
	$sql = "INSERT INTO `citadel_users` (character_id, is_admin) VALUES ('$characterID', '1');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_users_select($characterID) {
    $conn = db_conn();
    $sql = "SELECT id,character_id FROM `citadel_users` WHERE character_id = '$characterID' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	return $result;
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

function citadeldb_users_check_admin($id) {
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


// Session functions
function citadeldb_session_add($user_id, $session_key, $expire) {
    $conn = db_conn();
    $sql = "INSERT INTO `citadel_session` (user_id, session_key, expire) VALUES ('$user_id', '$session_key', '$expire');";
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
    $sql = "UPDATE `citadel_session` SET session_key = '$session_key' WHERE user_id = '$user_id';";
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
    $sql = "DELETE FROM `citadel_session` WHERE session_key = '$key';";
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
    $sql = "SELECT * FROM `citadel_session` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_session_get_id($key) {
    $conn = db_conn();
    $sql = "SELECT user_id FROM `citadel_session` WHERE session_key = '$key' LIMIT 1;";
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
    $sql = "SELECT session_key FROM `citadel_session` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['session_key'])) {
		return $result['session_key'];
	} else {
		return null;
	}
}

function citadeldb_session_check_key($key) {
    $conn = db_conn();
    $sql = "SELECT session_key FROM `citadel_session` WHERE session_key = '$key' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['session_key'])) {
		return true;
	} else {
		return false;
	}
}

// Cache functions
function citadeldb_cache_alliance_get($id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_cache_alliances` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_cache_character_get($id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_cache_characters` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_cache_corporation_get($id) {
    $conn = db_conn();
    $sql = "SELECT * FROM `citadel_cache_corporations` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

// Storage Tokens Functions
function citadeldb_token_add($user_id, $token_access, $token_refresh, $scope, $updated) {
    $conn = db_conn();
    $sql = "INSERT INTO `citadel_esi_tokens` (user_id, token_access, token_refresh, scope, updated) VALUES ('$user_id', '$token_access', '$token_refresh', '$scope', '$updated');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_token_update($user_id, $token_access, $scope, $updated) {
    $conn = db_conn();
	$sql = "UPDATE `citadel_esi_tokens` SET token_access = '$token_access', updated = '$updated' WHERE user_id = '$user_id' AND scope = '$scope';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_token_updatefull($user_id, $token_access, $token_refresh, $scope, $updated) {
    $conn = db_conn();
	$sql = "UPDATE `citadel_esi_tokens` SET token_access = '$token_access', token_refresh = '$token_refresh', updated = '$updated' WHERE user_id = '$user_id' AND scope = '$scope';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadeldb_token_get($user_id, $scope) {
    $conn = db_conn();
	$sql = "SELECT token_access, token_refresh, updated FROM `citadel_esi_tokens` WHERE user_id = '$user_id' AND scope = '$scope' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['token_access']) && isset($result['token_refresh'])) {
		return $result;
	} else {
		return null;
	}
}

function citadeldb_token_del($user_id, $scope) {
    $conn = db_conn();
	$sql = "DELETE FROM `citadel_esi_tokens` WHERE user_id = '$user_id' AND scope = '$scope';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

// Storage Custom Functions
function citadeldb_custom_add($custom_key, $custom_value) {
    $conn = db_conn();
    $sql = "INSERT INTO `citadel_custom` (custom_key, custom_value) VALUES ('$custom_key', '$custom_value');";
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
	$sql = "UPDATE `citadel_custom` SET custom_value = '$custom_value' WHERE custom_key = '$custom_key';";
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
	$sql = "SELECT custom_value FROM `citadel_custom` WHERE custom_key = '$custom_key' LIMIT 1;";
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
	$sql = "DELETE FROM `citadel_custom` WHERE custom_key = '$custom_key';";
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
	$sql = "INSERT INTO `teamspeak_users` (user_id, teamspeak_token) VALUES ('$user_id', '$token');";
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
    $sql = "SELECT teamspeak_uid,teamspeak_token FROM `teamspeak_users` WHERE user_id = '$user_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['teamspeak_token'])) {
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