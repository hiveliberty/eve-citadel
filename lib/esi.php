<?php
// ESI lib
//esi-mail.read_mail.v1
//esi-characters.read_notifications.v1
//esi-alliances.read_contacts.v1
//$esi_language = "en-us";

require_once(__DIR__ . '/../lib/cURL.php');

function esi_alliance_get_contacts($id, $token) {
	$datasource = "tranquility";
	$url = "/v1/alliances/{$id}/contacts/?datasource={$datasource}&token={$token}";
	$data = request_esi($url);
	return $data;
}

function esi_alliance_get_corporations($id) {
	$datasource = "tranquility";
	$url = "/v1/alliances/{$id}/corporations/?datasource={$datasource}";
	$data = request_esi($url);
	return $data;
}

function esi_alliance_get_details($id) {
	$datasource = "tranquility";
	$url = "/v3/alliances/{$id}/?datasource={$datasource}";
	$data = request_esi($url);
	return $data;
}

function esi_character_get_details($id) {
	$datasource = "tranquility";
	$url = "/v4/characters/{$id}/?datasource={$datasource}";
	$data = request_esi($url);
	if (!isset($data['alliance_id'])) {
		$data['alliance_id'] = 1;
	}
	return $data;
}

function esi_corporation_get_details($id) {
	$datasource = "tranquility";
	$url = "/v4/corporations/{$id}/?datasource={$datasource}";
	$data = request_esi($url);
	return $data;
}