<?php

namespace payla\library\cache;

class MemCache {
	private $expire;

	public $prefix;
	public $compressed;

	public function __construct($expire, $options) {
		$this->expire = $expire;
		$this->prefix = $options['prefix'];
		$this->compressed = $options['compressed'];

		$this->cache = new new \Memcache();
		$this->cache->pconnect($options['hostname'], $options['port']);
	}

	public function get($key) {
		return $this->cache->get($this->prefix . $key);
	}

	public function set($key,$value) {
		return $this->cache->set($this->prefix . $key, $value, $this->compressed, $this->expire);
	}

	public function delete($key) {
		$this->cache->delete($this->prefix . $key);
	}
}