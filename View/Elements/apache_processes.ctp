<?php
if (empty($title)) {
	$title = 'Apache Processes';
}
$this->Table->reset();
if (!empty($processes)) {
	foreach ($processes as $process) {
		$this->Table->cells(array(
			array($process['pid'], 'PID'),
			array($process['cpu'].'%', 'CPU'),
			array($process['mem'].'%', 'Mem'),
			array($process['time'], 'Time'),
			array($process['command'], 'Command'),
			array($this->Html->link('Kill', array('action' => 'kill_apache_process', $process['pid'])))
		), true);
	}
}
echo $this->Layout->contentBox($title, $this->Table->output(array('empty' => 'No Processes')), array('class' => 'list'));