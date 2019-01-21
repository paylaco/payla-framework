<?php

namespace payla\library;

class Db {
	private $db;

	public $driver;
	public $hostname;
	public $username;
	public $password;
	public $dbname;

	public function init() {
		$class = 'payla\\library\\db\\' . $this->driver;

		if (class_exists($class)) {
			$this->db = new $class($this->hostname, $this->username, $this->password, $this->dbname);
		} else {
			exit('Error: Could not load database driver ' . $this->driver . '!');
		}
	}

	public function query($sql) {
		return $this->db->query($sql);
	}

	public function escape($value) {
		return $this->db->escape($value);
	}

	public function countAffected() {
		return $this->db->countAffected();
	}

	public function getLastId() {
		return $this->db->getLastId();
	}
}
