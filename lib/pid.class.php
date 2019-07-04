<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

require_once(__DIR__ . '/../lib/other.php');

class PIDManager {

	private $rundir = "/run";

	function __construct($name) {
		// $this->logger = get_logger("pidmanager", "NOTICE", false);
		// $this->name = $name;
		$this->pidfile = $this->rundir . "/" . $name . ".pid";
	}

	private function start() {
		if ($this->is_running()) {
			return false;
		} else {
			$pid = fopen($this->pidfile, "wb");
			fclose($pid);
			return true;
		}
	}

	private function stop() {
		if (file_exists($this->pidfile)) {
			unlink($this->pidfile);
		}
	}

	private function is_running() {
		if (file_exists($this->pidfile)) {
			$file_time = filemtime($this->pidfile)+30*60;
			$now_time = time();
			if ($now_time > $file_time) {
				$this->set_status('stop');
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	function set_status($state) {
		switch ($state) {
			case 'start':
				return $this->start();
				break;
			case 'stop':
				$this->stop();
				break;
			default:
				break;
		}
	}
}
