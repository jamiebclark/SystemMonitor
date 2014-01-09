<?php
class MysqlProcess extends SystemMonitorAppModel {
	public $name = 'MysqlProcess';
	public $useTable = false;
	
	public $findMethods = array('processes' => true);
	private $PDO;
	
	public function __construct($id = false, $table = null, $ds = null) {
		$this->_mysqlOpen();
		parent::__construct($id, $table, $ds);
	}
	
	public function __destruct() {
		$this->_mysqlClose();
	}
	
	public function find($type = 'first', $options = array()) {
		if ($type == 'all') {
			$result = $this->findProcesses();
		}
		return $result;
	}
	
	public function kill($processId) {
		if (is_array($processId)) {
			foreach ($processId as $id) {
				$this->kill($id);
			}
		} else {
			return $this->PDO->query(sprintf('KILL %d', $processId));
		}
	}
	
	public function findProcesses() {
		$STH = $this->PDO->query('SHOW FULL PROCESSLIST');
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		$result = array();
		while ($row = $STH->fetch()) {
			$result[] = $row;
		}
		return $result;
	}
	
	private function _mysqlOpen($options = array()) {
		//Opens connection
		App::uses('ConnectionManager', 'Model');
		$dataSource = ConnectionManager::getDataSource('default');
		$this->PDO = new PDO(sprintf('mysql:host=%s;dbname=%s', 
				$dataSource->config['host'],
				$dataSource->config['database']), 
			$dataSource->config['login'],
			$dataSource->config['password']
		);
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	private function _mysqlClose() {
		$this->PDO = null;	//Closes connection
	}

}