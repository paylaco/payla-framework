<?php

namespace payla\library;

use Payla;

class Config {
	private $data = [];

	public function init(){}

	public function loadSettings(){
		$db = Payla::app()->db;

		// Settings
		$query = $db->query("SELECT * FROM `" . $db->prefix . "setting` WHERE 1");

		foreach ($query->rows as $result) {
		    if (!$result['serialized']) {
		        $this->set($result['key'], $result['value']);
		    } else {
		        $this->set($result['key'], unserialize($result['value']));
		    }
		}
	}

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function has($key) {
		return isset($this->data[$key]);
	}
}