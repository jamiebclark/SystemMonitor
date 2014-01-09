<?php
class StatusController extends SystemMonitorAppController {
	public $name = 'Status';
	public $uses = array('SystemMonitor.MysqlProcess', 'SystemMonitor.Apache');
	
	public function admin_index() {
		$expand = !empty($this->request->params['named']['expand']) ? $this->request->params['named']['expand'] : null;
		$userName = !empty($this->request->params['named']['user']) ? $this->request->params['named']['user'] : null;
		
		$apacheStatus = $this->Apache->find('status');
		$mysqlProcesses = $this->MysqlProcess->find('all', compact('userName'));
		
		$this->set(compact('apacheStatus', 'mysqlProcesses', 'expand', 'userName'));
	}
}