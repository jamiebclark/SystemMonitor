<?php
class StatusController extends SystemMonitorAppController {
	public $name = 'Status';
	public $uses = array('SystemMonitor.MysqlProcess', 'SystemMonitor.Apache');
	public $helpers = array('SystemMonitor.SystemMonitor');
	
	public function admin_index() {
		$expand = !empty($this->request->params['named']['expand']) ? $this->request->params['named']['expand'] : null;
		$userName = !empty($this->request->params['named']['user']) ? $this->request->params['named']['user'] : null;
		
		$apacheStatus = $this->Apache->find('all');
		$mysqlProcesses = $this->MysqlProcess->find('all', compact('userName'));
		
		debug($apacheStatus);
		
		$this->set(compact('apacheStatus', 'mysqlProcesses', 'expand', 'userName'));
	}
	
	public function admin_mysql_processes() {
		$mysqlProcesses = $this->MysqlProcess->find('all', compact('userName'));
		$this->set(compact('mysqlProcesses'));
		if ($this->request->is('ajax')) {
			$this->render('SystemMonitor./Elements/mysql_processes');
		}
	}
	
	public function admin_apache_status() {
		$apacheStatus = $this->Apache->find('status');
		$this->set(compact('apacheStatus'));
		if ($this->request->is('ajax')) {
			$this->render('SystemMonitor./Elements/apache_status');
		}
	}
}