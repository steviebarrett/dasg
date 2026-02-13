<?php

namespace models;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
class database {

	private $_dbh, $_sth;

	/**
	 * Creates and initialises a new Database object
	 */
	public function __construct($dbName = DB) {
		try {
			$options = [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES => true,
				\PDO::MYSQL_ATTR_LOCAL_INFILE => false,
			];
			$this->_dbh = new \PDO(
				"mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=utf8;", DB_USER, DB_PASSWORD, $options
			);
		} catch (\PDOException $e){
			error_log("DB connect error: " . $e->getMessage());
			$this->_dbh = null;
		}
	}

	public function getDatabaseHandle() {
		return $this->_dbh;
	}

	public function __destruct() {
		$this->_dbh = null;
		$this->_sth = null;
	}

	/**
	 * A simple fetch function to run a prepared query
	 *
	 * @param string $sql      : The SQL for the query
	 * @param array $values    : The params for the query (defaults to empty)
	 * @return array $results  : The results array
	 */
	public function fetch($sql, $values = array()) {
		if (!$this->_dbh) {
			return [];
		}
		try {
			$this->_sth = $this->_dbh->prepare($sql);
			$this->_sth->execute($values);
			$results = $this->_sth->fetchAll();
			return $results;
		} catch (\PDOException $e) {
			error_log("DB fetch error: " . $e->getMessage());
			return [];
		}
	}

	/**
	 * A simple execute function to run a prepared query
	 *
	 * @param string $sql      : The SQL for the query
	 * @param array $values    : The params for the query (defaults to empty)
	 */
	public function exec($sql, array $values = array()) {
		if (!$this->_dbh) {
			return false;
		}
		try {
			$this->_sth = $this->_dbh->prepare($sql);
			$this->_sth->execute($values);
			return true;
		} catch (\PDOException $e) {
			error_log("DB exec error: " . $e->getMessage());
			return false;
		}
	}

	public function getLastInsertId() {
		return $this->_dbh->lastInsertId();
	}
}
