<?php 
extract($apacheStatus);

if ($expand) {
	$title = 'Expanded Info';
	$link = array('Condense', array(0));
} else {
	$title = 'Condensed Info';
	$link = array('Expand', array(1));
}
?>
<div id="apacheStatus">
<p class="note"><?php echo $this->Html->link(
		'Last pulled: ' . date('F j, Y g:i:s a'),
		Router::url()
	);?>
</p> 
<p><strong><?php echo $title; ?></strong> [<?php echo $this->Html->link($link[0], $link[1]); ?>]</p>

<?php 
//CPU
echo $this->Layout->contentBoxOpen('CPU Usage');
extract($topStatus['cpu']);
echo $this->Html->tag('h3');
echo $this->Html->tag(
	'font', 
	$unused.'% Idle',
	array(
		'style' => 'color:'.$this->TextGraph->colorRange($unused, 0, $unused + $used)
	)
);
echo ' ';
echo $this->Html->tag(
	'font', 
	$used . '% Used',
	array(
		'style' => 'color:' . $this->TextGraph->colorRange($unused, 0, $unused + $used)
	)
);
echo "</h3>\n";

echo $this->TextGraph->barGraph($unused,$used);


if($expand) {
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
}
echo $this->Html->tag('p', 'Loaded in ' . number_format($time / 1000, 2) . ' seconds');
echo $this->Layout->contentBoxClose();


//Memory
echo $this->Layout->contentBoxOpen('Memory Monitor');
extract($memInfo['total']);
echo $this->Html->tag('h2', $this->Number->toReadableSize($total) . ' Memory');

echo $this->Html->tag('h3');
echo $this->Html->tag(
	'font', 
	$this->Number->toReadableSize($used) . ' Used',
	array('style' => 'color:' . $this->TextGraph->colorRange($free, 0, $total))
);
echo ' ';
echo $this->Html->tag(
	'font', 
	$this->Number->toReadableSize($free) . ' Idle',
	array('style' => 'color:' . $this->TextGraph->colorRange($free, 0, $total))
);
echo "</h3>\n";

echo $this->TextGraph->barGraph($free,$used);

if($expand) {
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
}
echo $this->Html->tag('p', 'Loaded in ' . number_format($time / 1000, 2) . ' seconds');
echo $this->Layout->contentBoxClose();


//Apache status
echo $this->Layout->contentBoxOpen('Apache Status', array('id' => 'apacheStatus'));
extract($apacheStatus);
echo $this->Html->tag('h2', number_format($total) . ' Ports');

echo $this->Html->tag('h3');
echo $this->Html->tag(
	'font', 
	($used) . ' Used',
	array('style' => 'color:' . $this->TextGraph->colorRange($unused, 0, $total))
);
echo ' ';
echo $this->Html->tag(
	'font', 
	($unused) . ' Open',
	array('style' => 'color:' . $this->TextGraph->colorRange($unused, 0, $total))
);
echo "</h3>\n";
echo $this->TextGraph->barGraph($unused,$used);

if($expand) {
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
echo $this->Html->tag('p', 'Loaded in ' . number_format($time / 1000, 2) . ' seconds');
echo $this->Layout->contentBoxClose();
echo "</div>\n";
?>