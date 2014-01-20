<?php 
debug($apacheStatus);
extract($apacheStatus);
if ($expand) {
	$title = 'Expanded Info';
	$link = array('Condense', array(0));
} else {
	$title = 'Condensed Info';
	$link = array('Expand', array(1));
}
$top = array();

//CPU Usage
$top['CPU Usage'] = array($topStatus['cpu']['used'], 100, '%', $topStatus['cpu']['time']);
$top['Memory'] = array($topStatus['memory']['used'], $topStatus['memory']['total'], 'size', $topStatus['memory']['time']);
$top['Apache Status'] = array($apacheStatus['used'], $apacheStatus['total'], 'Ports', $apacheStatus['time']);

?>
<div id="apachestatus">
	<p class="note"><?php echo $this->Html->link(
			'Last pulled: ' . date('F j, Y g:i:s a'),
			Router::url()
		);?>
	</p> 
	<p><strong><?php echo $title; ?></strong> [<?php echo $this->Html->link($link[0], $link[1]); ?>]</p>

	<div class="row-fluid">
	<?php foreach ($top as $title => $stats): 
		list($used, $total, $type) = $stats + array(null, null, null);
		?>
		<div class="span4">
			<?php echo $this->SystemMonitor->statusBox($title, $used, $total, $type); ?>
		</div>
	<?php endforeach ?>
	</div>
<?php

if($expand || true) {
	//Memory
	echo $this->SysemtMonitor->memoryDetail($memory['detail']);

	//CPU
	echo $this->SystemMonitor->cpuDetail($cpu['breakdown']);
	
	//Apache Status
	echo $this->SystemMonitor->apacheDetail($apache['breakdown']);
}
?>
</div>