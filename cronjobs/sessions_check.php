<?php

require_once(__DIR__ . '/../lib/db.php');

$session_keys = citadeldb_session_getall();
foreach ($session_keys as $session_key) {
	if (isset($session_key['session_key'])) {
		if (strtotime($session_key['expire_date']) <= time()) {
			citadeldb_session_delete($session_key['session_key']);
		}
	}
}

?>