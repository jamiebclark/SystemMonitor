<?php
class MysqlProcessesController extends SystemMonitorAppController {
	public $name = 'MysqlProcesses';
	public $components = array('Layout.Table');
	
	public function admin_kill($id) {
		try {
			$this->MysqlProcess->kill($id);
		} catch(PDOException $e) {
			$this->Flash->error($e->getMessage());
		}
		$this->redirect($this->referer());
	}
	
	public function _withChecked($type, $ids, $options = array()) {
		if ($type == 'kill') {
			$options['result'] = $this->MysqlProcess->kill($ids);
		}
		return $options;
	}
}