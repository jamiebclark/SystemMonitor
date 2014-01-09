<?php
App::uses('Debugger', 'Utility');
class Apache extends SystemMonitorAppModel {
	public $name = 'Apache';
	public $useTable = false;
	
	public $findMethods = array('status' => true);
	
	
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
	
	public function find($type = 'first', $query = array()) {
		$result = array();
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