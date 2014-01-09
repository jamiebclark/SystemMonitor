<?php
echo $this->element('system_monitors/tabs');
echo $this->Html->tag('h1', 'MySQL Status');
echo $this->Layout->headerMenu(array(array('Kill Sleeping Processes', array('action' => 'kill_sleeping'))));
echo $this->element('system_monitors/mysql_processes');