<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/lib/token.php');
require_once(__DIR__ . '/lib/db.php');
require_once(__DIR__ . '/lib/esi.php');

if (isset($argv[1])) {
	switch ($argv[1]) {
		case 'addmember':
			$member_id = citadeldb_custom_get("member_id");
			if ($member_id == null) {
				citadeldb_custom_add("member_id", $argv[2]);
			} else {
				citadeldb_custom_update("member_id", $argv[2]);
			}
			print_r("Added {$argv[2]} as member.\n");
			break;
		case 'checkmember':
			$member_id = citadeldb_custom_get("member_id");
			if ($member_id == null) {
				print_r("No member is set.\n");
			} else {
				print_r("Member is {$member_id}.\n");
			}
			break;
		default:
			die("Unknown command");
			break;
	}
}

?>