<?php
$tabs = array(
	array('Home', array('action' => 'index')),
	array('MySQL', array('action' => 'processes')),
	array('Apache', array('action' => 'apache')),
);
echo $this->Layout->tabMenu($tabs);