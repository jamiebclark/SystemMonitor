<?php
$rowCount = 0;
$this->Table->reset();
foreach ($mysqlProcesses as $row) {
	$id = $row['Id'];
	
	if ($row['Command'] == 'Sleep') {
		$query = 'Sleep';
	} else {
		$query = $row['Info'];
	}
	
	$cells = array(
		'User', 
		'Host', 
		'db' => 'Database', 
		'Command', 
		'Time', 
		'State' => 'Status', 
		'Info' => 'SQL Query'
	);
	
	$this->Table->checkbox($id);
	$this->Table->cell(++$rowCount, '#');
	$this->Table->cell($this->Html->link(
		'Kill',
		array('action' => 'kill_process', $id, $userName)
	), 'Kill');
	
	foreach ($cells as $key => $columnTitle) {
		if (is_numeric($key)) {
			$key = $columnTitle;
		}
		
		if (empty($row[$key])) {
			$row[$key] = '&nbsp;';
		}
		
		$this->Table->cell($row[$key], $columnTitle);
	}
	
	$this->Table->rowEnd();
}
echo $this->Layout->contentBoxOpen('MySQL Processes', array('id' => 'mysqlProcesses'));
echo $this->Table->output(array(
	'empty' => $this->Html->div('lead', 'No Processes found'),
	'withChecked' => array('kill'),
	'form' => array('model' => 'SystemMonitor.MysqlProcess'),
));
echo $this->Layout->contentBoxClose();