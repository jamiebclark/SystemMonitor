<?php
App::uses('Debugger', 'Utility');
class Apache extends SystemMonitorAppModel {
	public $name = 'Apache';
	public $useTable = false;
	
	public $findMethods = array(
		'cpu' => true,
		'memory' => true,
		'status' => true,
		'statusList' => true,
		'top' => true,
		'processes' => true,
		'oldProcesses' => true
	);
	
	
	private $statusVars = array(
		'oldProcesses',
		'processes',
		'memInfo',
		'apacheStatusKeys',
		'topCpuKeys',
		'topStatus',
		'apacheStatus',
	);
	
	public function __construct($id = false, $table = null, $ds = null) {
		App::import('Vendor', 'SystemMonitor.ApacheMonitor');
		$this->ApacheMonitor = new ApacheMonitor();
		parent::__construct($id, $table, $ds);
	}
	
	public function findTopStatus($type = 'all') {
		$topStatuses = array('cpu', 'memory');
		$result = array();
		if ($type = 'all') {
			foreach ($topStatuses as $key) {
				$result += $this->findTopStatus($key);
			}
		} else if (in_array($type, $topStatuses)) {
			$top = $this->ApacheMonitor->getTopStatus();
			$result[$type] = $top[$type];
		}
	}
	
	public function find($type = 'first', $query = array()) {
		$result = array();
		switch ($type) {
			case 'apache':
				$result['apache'] = $this->ApacheMonitor->getApacheStatus();
			break;
			case 'cpu':
				$result = $this->findTopStatus('cpu');
			break;
			case 'memory':
				$result = $this->findTopStatus('cpu');
			break;
			case 'processes':
				$result['processes'] = $this->ApacheMonitor->getProcesses();
			break;
			case 'top':
			break;
			case 'oldProcesses':
			break;
			case 'statuses':
			break;
		}
		if ($type == 'status') {
			foreach ($this->statusVars as $var) {
				$result[$var] = $this->find($var);
			}
		} else if (in_array($type, $this->statusVars)) {
			$getter = 'get' . ucfirst($type);
			if (method_exists($this->ApacheMonitor, $getter)) {
				$result = $this->ApacheMonitor->$getter();
			} else {
				$result = $this->ApacheMonitor->$type;
			}
		}
		return $result;
	}
}