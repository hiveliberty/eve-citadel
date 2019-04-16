<?php
// Some custom lib

require_once(__DIR__ . '/../lib/cURL.php');
require_once(__DIR__ . '/../lib/db.class.php');

function get_token($character_id, $scope) {
	$app_config = require __DIR__ . '/../config/app.php';
	$db_client = new citadelDB();
	$token_data = $db_client->token_get($character_id, $scope);
	if (isset($token_data['refresh_token'])) {
		if (strtotime($token_data['expire_date']) <= time()) {
			$base64 = base64_encode($app_config['sso']['clientID'] . ':' . $app_config['sso']['secretKey']);
			$token_data = request_access_token($base64, $token_data['refresh_token']);
			$expire_date =  time()+19*60;
			$expire_date = date("Y-m-d H:i:s", $expire_date);
			$access_token = $token_data['access_token'];
			$db_client->token_update($character_id, $access_token, $scope, $expire_date);
			unset($db_client);
			return $access_token;
		} else {
			unset($db_client);
			return $token_data['access_token'];
		}
	} else {
		unset($db_client);
		return null;
	}
}
