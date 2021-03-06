<?php

namespace payla\library\db;

final class mPDO {

	private $pdo = null;
	private $statement = null;

	public function __construct($hostname, $username, $password, $database, $port = "3306") {
		try {
			$this->pdo = new \PDO("mysql:host=" . $hostname . ";port=" . $port . ";dbname=" . $database, $username, $password, [\PDO::ATTR_PERSISTENT => true]);
		} catch(\PDOException $e) {
			trigger_error('Error: Could not make a database link ( ' . $e->getMessage() . '). Error Code : ' . $e->getCode() . ' <br />');
		}

		$this->pdo->exec("SET NAMES 'utf8'");
		$this->pdo->exec("SET CHARACTER SET utf8");
		$this->pdo->exec("SET CHARACTER_SET_CONNECTION=utf8");
		$this->pdo->exec("SET SQL_MODE = ''");

	}

	public function prepare($sql) {
		$this->statement = $this->pdo->prepare($sql);
	}

	public function bindParam($parameter, $variable, $data_type = \PDO::PARAM_STR, $length = 0) {
		if ($length) {
			$this->statement->bindParam($parameter, $variable, $data_type, $length);
		} else {
			$this->statement->bindParam($parameter, $variable, $data_type);
		}
	}

	public function execute() {
		try {
			if ($this->statement && $this->statement->execute()) {
				$data = [];

				while ($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0])) ? $data[0] : [];
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();
			}
		} catch(\PDOException $e) {
			trigger_error('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode());
		}
	}

	public function query($sql, $params = []) {
		$this->statement = $this->pdo->prepare($sql);
		$result = false;

		try {
			if ($this->statement && $this->statement->execute($params)) {
				$data = [];

				while ($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0]) ? $data[0] : []);
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();
			}
		} catch (\PDOException $e) {
			trigger_error('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
			exit();
		}

		if ($result) {
			return $result;
		} else {
			$result = new \stdClass();
			$result->row = [];
			$result->rows = [];
			$result->num_rows = 0;
			return $result;
		}
	}

	public function escape($value) {
		$search = ["\\", "\0", "\n", "\r", "\x1a", "'", '"'];
		$replace = ["\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'];
		return str_replace($search, $replace, $value);
	}

	public function countAffected() {
		if ($this->statement) {
			return $this->statement->rowCount();
		} else {
			return 0;
		}
	}

	public function getLastId() {
		return $this->pdo->lastInsertId();
	}

	public function __destruct() {
		$this->pdo = null;
	}
}