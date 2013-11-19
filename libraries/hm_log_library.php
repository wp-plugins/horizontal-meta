<?php

class hm_log_library extends hmeta_library_base {

	function is_logging_enabled() {
		return false;
	}

	function add_to_log($log) {
		if(!$this->is_logging_enabled())
			return;

		$log_file = $this->get_log_file();
		$fid = fopen($log_file, "a");
		if(substr($log, -1) != "\n") $log .= "\n";
		$log = date("Y-m-d H:i:s", time()) . ": " . $log;
		fwrite($fid, $log);
		fclose($fid);
	}

	function new_grouping() {
		$log_file = $this->get_log_file();
		$fid = fopen($log_file, "a");
		fwrite($fid, "\n\n\n");
		fclose($fid);
	}

	function get_log() {
		$log_file = $this->get_log_file();
		file_get_contents($log_file);
	}

	function clear_log() {
		$log_file = $this->get_log_file();
		file_put_contents($log_file, "");
	}

	function get_log_file() {
		$log_dir = $this->get_plugin_dir() . "/logs/meta_log.log";
		return $log_dir;
	}
}