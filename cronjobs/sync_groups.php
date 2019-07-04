<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../lib/pid.class_new.php');
require_once(__DIR__ . '/../lib/sync.class.php');

$pidmanager = new PIDManager("eve-sync");
if (!$pidmanager->set_status('start')) {
	print_r("[".date("Y-m-d H:i:s", time())."] Synchronization is already running..\n");
	die();
}

$sync = new SyncManager();
print_r("[".date("Y-m-d H:i:s", time())."] Sync owner corporations.\n");
$sync->corp_groups();
print_r("[".date("Y-m-d H:i:s", time())."] Sync service roles.\n");
$sync->server_groups();
print_r("[".date("Y-m-d H:i:s", time())."] Sync user roles.\n");
$sync->user_groups();

$pidmanager->set_status('stop');
return;
?>
