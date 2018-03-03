<?php

require_once(__DIR__ . '/../lib/db.class.php');

print_r("[".date("Y-m-d H:i:s", time())."] Start cleanup session keys from DB.\n");
$db_client = new citadelDB();
$session_keys = $db_client->session_get_all();
foreach ($session_keys as $session_key) {
	if (isset($session_key['session_key'])) {
		if (strtotime($session_key['expire_date']) <= time()) {
			$db_client->session_delete($session_key['session_key']);
		}
	}
}
unset($db_client);

?>