<?php
// Some custom lib

function citadelUsersInsert($db, $user, $pass, $dbName, $characterID)
{

    $conn = new mysqli($db, $user, $pass, $dbName);

    $sql = "INSERT INTO `citadel_users` SET character_id = '$characterID';";

    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadelUsersSelect($db, $user, $pass, $dbName, $characterID)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT id,character_id FROM `citadel_users` WHERE character_id = '$characterID' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	return $result;
}

function citadelUsersSelectById($db, $user, $pass, $dbName, $id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT character_id FROM `citadel_users` WHERE id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['character_id'])) {
		return $result['character_id'];
	} else {
		return null;
	}
}


// Session functions
function citadelSessionSet($db, $user, $pass, $dbName, $user_id, $session_key, $expire)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "INSERT INTO `citadel_session` (user_id, session_key, expire) VALUES ('$user_id', '$session_key', '$expire');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadelSessionUpdate($db, $user, $pass, $dbName, $user_id, $session_key)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "UPDATE `citadel_session` SET session_key = '$session_key' WHERE user_id = '$user_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadelSessionDelete($db, $user, $pass, $dbName, $key)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "DELETE FROM `citadel_session` WHERE session_key = '$key';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function citadelSessionGet($db, $user, $pass, $dbName, $id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT * FROM `citadel_session` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result)) {
		return $result;
	} else {
		return null;
	}
}

function citadelSessionGetId($db, $user, $pass, $dbName, $key)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT user_id FROM `citadel_session` WHERE session_key = '$key' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['user_id'])) {
		return $result['user_id'];
	} else {
		return null;
	}
}

function citadelSessionGetKey($db, $user, $pass, $dbName, $id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT session_key FROM `citadel_session` WHERE user_id = '$id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['session_key'])) {
		return $result['session_key'];
	} else {
		return null;
	}
}

function citadelSessionCheckKey($db, $user, $pass, $dbName, $key)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT session_key FROM `citadel_session` WHERE session_key = '$key' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['session_key'])) {
		return true;
	} else {
		return false;
	}
}

function discordUsersInsert($db, $user, $pass, $dbName, $user_id, $discord_id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
	$sql = "INSERT INTO `discord_users` (user_id, discord_id) VALUES ('$user_id', '$discord_id');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function discordUsersSelect($db, $user, $pass, $dbName, $user_id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT discord_id FROM `discord_users` WHERE user_id = '$user_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['discord_id'])) {
		return $result['discord_id'];
	} else {
		return null;
	}
}

function discordUsersDelete($db, $user, $pass, $dbName, $user_id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
	$sql = "DELETE FROM `discord_users` WHERE user_id = '$user_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function teamspeakUsersInsert($db, $user, $pass, $dbName, $user_id, $token)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
	$sql = "INSERT INTO `teamspeak_users` (user_id, teamspeak_token) VALUES ('$user_id', '$token');";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}

function teamspeakUsersSelect($db, $user, $pass, $dbName, $user_id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
    $sql = "SELECT teamspeak_uid,teamspeak_token FROM `teamspeak_users` WHERE user_id = '$user_id' LIMIT 1;";
    $result = $conn->query($sql)->fetch_assoc();
	$conn->close();
	if (isset($result['teamspeak_token'])) {
		return $result;
	} else {
		return null;
	}
}

function teamspeakUsersDelete($db, $user, $pass, $dbName, $user_id)
{
    $conn = new mysqli($db, $user, $pass, $dbName);
	$sql = "DELETE FROM `teamspeak_users` WHERE user_id = '$user_id';";
    if ($conn->query($sql) === TRUE) {
		$conn->close();
        return null;
    } else {
		$conn->close();
        return null;
    }
}