<?php
// Some custom lib

function getData($url)
{
    try
    {
        $userAgent = "EVE Citadel Auth System";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        $headers = array();
        $headers[] = "Connection: keep-alive";
        $headers[] = "Keep-Alive: timeout=10, max=1000";
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);

        return $result;
    }
    catch(Exception $e)
    {
        var_dump("cURL Error: " . $e->getMessage());
        return null;
    }
}

function sendData($url, $postData = array(), $headers = array()) {
    $userAgent = "EVE Citadel Auth System";

    // Define default headers
    if (empty($headers)) {
        $headers = array('Connection: keep-alive', 'Keep-Alive: timeout=10, max=1000');
    }

    // Init curl
    $curl = curl_init();

    // Init postLine
    $postLine = '';

    // Populate the $postData
    if (!empty($postData)) {
        foreach ($postData as $key => $value) {
            $postLine .= $key . '=' . $value . '&';
        }
    }

    // Trim the last &
    rtrim($postLine, '&');

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    if (!empty($postData)) {
        curl_setopt($curl, CURLOPT_POST, count($postData));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postLine);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function request_esi($url) {
	try {
		$esi_request = "https://esi.tech.ccp.is" . $url;
		$user_agent = "EVE Citadel Auth System";
		$headers = array("Accept: application/json",);

		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_USERAGENT => $user_agent,
				CURLOPT_URL => $esi_request,
				CURLOPT_HTTPHEADER => $headers,
				//CURLOPT_POST => true,
				//CURLOPT_POSTFIELDS => $params,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_TIMEOUT => 16
			)
		);

		$result = curl_exec($curl);
		curl_close($curl);

		$result = json_decode($result, TRUE);
		return $result;
    }
	catch(Exception $e) {
		return null;
    }
}

function request_access_token($base64, $refresh_token) {
	try {
		$url = "https://login.eveonline.com/oauth/token";
		$user_agent = "EVE Citadel Auth System";

		$headers = array(
			"Authorization: Basic {$base64}",
			"Content-Type: application/json",
			"Host: login.eveonline.com",
		);
		
		$params = json_encode(array(
			"grant_type" => "refresh_token",
			"refresh_token" => $refresh_token,
		));
		
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_USERAGENT => $user_agent,
				CURLOPT_URL => $url,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $params,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
			)
		);

		$result = curl_exec($curl);
		//$info = curl_getinfo($curl);
		//$err  = curl_errno($curl);
		//$errmsg = curl_error($curl);

		curl_close($curl);

		$result = json_decode($result, TRUE);
		return $result;
    }
	catch(Exception $e) {
		return null;
    }
}