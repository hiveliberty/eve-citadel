<?php
// ESI lib

function makeEsiRequest($url)
{
	try {
		$esi_request = "https://esi.tech.ccp.is" . $url;
		$userAgent = "Citadel Auth System";

		$headers = array();
		$headers[] = "Accept: application/json";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($curl, CURLOPT_TIMEOUT, 16);
		curl_setopt($curl, CURLOPT_URL, $esi_request);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		$result = curl_exec($curl);
		curl_close($curl);

		$result = json_decode($result, TRUE);
		return $result;
    }
	catch(Exception $e) {
		return null;
    }
}

function characterGetDetails($characterID) {
	$esi_language = "en-us";
	$esi_datasource = "tranquility";
	$url = "/v4/characters/$characterID/?datasource=$esi_datasource";
	$data = makeEsiRequest($url);
	return $data;
}

function corporationGetDetails($corpID) {
	$esi_language = "en-us";
	$esi_datasource = "tranquility";
	$url = "/v4/corporations/$corpID/?datasource=$esi_datasource";
	$data = makeEsiRequest($url);
	return $data;
}

function allianceGetDetails($allianceID) {
	$esi_language = "en-us";
	$esi_datasource = "tranquility";
	$url = "/v3/alliances/$allianceID/?datasource=$esi_datasource";
	$data = makeEsiRequest($url);
	return $data;
}