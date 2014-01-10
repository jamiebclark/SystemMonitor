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
	$this->Table->reset();
	foreach($memInfo['detail'] as $label => $val) {
		$reverse = ($label != 'free');
		$color = $this->TextGraph->colorRange($val, 0, $total, $reverse);
		$this->Table->cells(array(
			array(
				$this->Number->toReadableSize($val),
				null, null, null, array('style'=>'color:'.$color)
			),
			array(
				$label,
				null, null, null, array('style'=>'color:'.$color)
			)
		), true);
	}
	echo $this->Table->output();

	//CPU
	$this->Table->reset();
	foreach($topCpuKeys as $key => $label) {
		$reverse = ($key != 'id');
		$color = $this->TextGraph->colorRange($breakdown[$key], 0, 100, $reverse);
		$this->Table->cells(array(
			array(
				$breakdown[$key] . '%',
				null, null, null, array('style'=>'color:'.$color)
			),
			array(
				$label,
				null, null, null, array('style'=>'color:'.$color)
			)
		), true);
	}
	echo $this->Table->output();

	$this->Table->reset();
	foreach ($apacheStatusKeys as $key => $label) {
		$val = !empty($breakdown[$key]) ? $breakdown[$key] : 0;
		$this->Table->cells(array(
			array(number_format($val)),
			array($key),
			array($label),
		), true);
		unset($breakdown[$key]);
	}
	foreach($breakdown as $key => $count) {
		$this->Table->cells(array(
			array(number_format($count)),
			array($key),
			array('&nbsp;'),
		), true);
	}
	echo $this->Table->output();
}
?>
</div>