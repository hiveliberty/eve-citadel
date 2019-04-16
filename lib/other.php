<?php
// Some custom lib
//require __DIR__ . '/../vendor/autoload.php';

function uniqidReal($lenght = 13) {
    // From php.net
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new Exception("no cryptographically secure random function available");
    }
    return substr(bin2hex($bytes), 0, $lenght);
}

function password_generate($len = 16) {
	// From https://github.com/sarciszewski/oauth2-server/blob/master/src/Util/KeyAlgorithm/DefaultAlgorithm.php
	$bytes = openssl_random_pseudo_bytes($len * 2, $strong);
	if ($bytes === false || $strong === false) {
		throw new \Exception('Error Generating Key');
	}
	return substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $len);
}

function make_arrayby_key($array, $key) {
	$custom_array = array();
	foreach ($array as $unit) {
		$custom_array[] = $unit[$key];
	}
	return $custom_array;
}

function corp_group_name($ticker) {
	$name = $ticker." Corporation";
	return $name;
}

function nick_formate($ticker, $name) {
	$name = $ticker." ".$name;
	return $name;
}

function get_logger($name = "citadel", $console = false) {
	$path = __DIR__ . '/../logs/'.$name.'.log';

	$output = "[%datetime%] %channel%.%level_name%: %message%\n";
	$formatter = new LineFormatter($output);
	$logger = new Logger($name);
	$log_handler_file = new StreamHandler($path, Logger::INFO);
	$log_handler_file->setFormatter($formatter);
	$logger->pushHandler($log_handler_file);
	if ($console) {
		$log_handler_console = new StreamHandler('php://stdout', Logger::INFO);
		$log_handler_console->setFormatter($formatter);
		$logger->pushHandler($log_handler_console);
	}
	// $logger->info('------------[ '.date("Y-m-d_H-i-s", time()).' ]------------');

	return $logger;
}
