<?php
$tabs = array(
	array('Home', array('action' => 'index')),
	array('MySQL', array('action' => 'processes')),
	array('MySQL Slow Queries', array('action' => 'slow_queries')),
	array('Apache', array('action' => 'apache')),
);
echo $this->Layout->tabMenu($tabs);
?>