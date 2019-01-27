<?php

namespace payla\library;

use Payla;

class Cache {
	private $cache;

	public $driver;
	public $expire;
	public $options = [];

	public function init() {
		$class = 'payla\\library\\cache\\' . $this->driver;

		if (class_exists($class)) {
			$this->cache = new $class($this->expire, $this->options);
		} else {
			exit('Error: Could not load cache driver ' . $this->driver . ' cache!');
		}
	}

	public function get($key) {
		return $this->cache->get($key);
	}

	public function set($key, $value) {
		return $this->cache->set($key, $value);
	}

	public function delete($key) {
		return $this->cache->delete($key);
	}
}
