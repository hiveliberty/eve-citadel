<?php
// Some custom lib

require_once(__DIR__ . '/../lib/cURL.php');
require_once(__DIR__ . '/../lib/db.php');

function get_token($user_id, $scope) {
	$app_config = require __DIR__ . '/../config/app.php';
	$token_data = citadeldb_token_get($user_id, $scope);
	if (isset($token_data['token_refresh'])) {
		if (strtotime($token_data['updated']) <= time()) {
			$base64 = base64_encode($app_config['sso']['clientID'] . ':' . $app_config['sso']['secretKey']);
			$token_data = request_access_token($base64, $token_data['token_refresh']);
			$expire_date =  time()+19*60;
			$expire_date = date("Y-m-d H:i:s", $expire_date);
			$access_token = $token_data['access_token'];
			citadeldb_token_update($user_id, $access_token, $scope, $expire_date);
			return $access_token;
		} else {
			return $token_data['token_access'];
		}
	} else {
		return null;
	}
}