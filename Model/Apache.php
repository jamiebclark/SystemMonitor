<?php
App::uses('Debugger', 'Utility');
App::import('Vendor', 'SystemMonitor.ApacheMonitor');

class Apache extends SystemMonitorAppModel {
	public $name = 'Apache';
	public $useTable = false;
	
	public $customMethods = array(
		'cpu' => true,
		'memory' => true,
		'status' => true,
		'statuses' => true,
		'top' => true,
		'processes' => true,
		'oldProcesses' => true,
		'apache' => true
	);
	
	public function __construct($id = false, $table = null, $ds = null) {
		$this->ApacheMonitor = new ApacheMonitor();
		foreach ($this->customMethods as $method => $true) {
			$this->findMethods[$method] = $true;
		}
		parent::__construct($id, $table, $ds);
	}
	
	public function findTopStatus($type = 'all') {
		$topStatuses = array('cpu', 'memory');
		$result = array();
		if ($type == 'all') {
			foreach ($topStatuses as $key) {
				$result += $this->findTopStatus($key);
			}
		} else if (in_array($type, $topStatuses)) {
			$top = $this->ApacheMonitor->getTopStatus();
			$result[$type] = $top[$type];
		}
		return $result;
	}
	
	public function find($type = 'first', $query = array()) {
		$result = array();
		switch ($type) {
			case 'all':
				foreach ($this->customMethods as $method => $true) {
					if ($true) {
						if ($row = $this->find($method)) {
							debug($row);
							$result += $row;
						}
					}
				}
			break;
			case 'apache':
				$result['apache'] = $this->ApacheMonitor->getApacheStatus();
			break;
			case 'cpu':
				$result = $this->findTopStatus('cpu');
			break;
			case 'memory':
				$result = $this->findTopStatus('cpu');
				$memInfo = $this->ApacheMonitor->getMemInfo();
				$result['memory']['detail'] = $memInfo['detail'];
			break;
			case 'processes':
				$result['processes'] = $this->ApacheMonitor->getProcesses();
			break;
			case 'oldProcesses':
				$result['oldProcesses'] = $this->ApacheMonitor->getOldProcesses();
			break;
			case 'statuses':
			break;
		}
		return $result;
	}
}