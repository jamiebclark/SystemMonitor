<?php
echo $this->element('system_monitors/tabs');
$menu = array();
if (!empty($oldProcesses)) {
	$menu[] = array('Kill Old Processes (' . count($oldProcesses) . ')', array('action' => 'apache_kill_old_processes'));
}
echo $this->Layout->headerMenu($menu);

echo $this->Grid->open('1/2', $this->element('system_monitors/apache_status'));
echo $this->Grid->colContinue('1/2');
if (!empty($oldProcesses)) {
	echo $this->element('system_monitors/apache_processes', array('title' => 'Old Processes','processes' => $oldProcesses));
}
echo $this->element('system_monitors/apache_processes', array('title' => 'Processes'));
echo $this->Grid->colClose(true);