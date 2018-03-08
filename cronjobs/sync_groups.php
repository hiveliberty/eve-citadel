<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../lib/sync.class.php');

$sync = new SyncManager();
print_r("[".date("Y-m-d H:i:s", time())."] Synchronize owner corporations.\n");
$sync->corp_groups();
print_r("[".date("Y-m-d H:i:s", time())."] Synchronize service roles.\n");
$sync->server_groups();
print_r("[".date("Y-m-d H:i:s", time())."] Synchronize user roles.\n");
$sync->user_groups();

?>