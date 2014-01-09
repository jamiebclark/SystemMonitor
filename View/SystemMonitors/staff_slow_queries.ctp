<?php
echo $this->element('system_monitors/tabs');
echo $this->Html->tag('h1', 'MySQL Slow Queries');
echo $this->Layout->headerMenu(array(
	array('Remove all "Sleep" Commands', array('action' => 'slow_queries', 1)),
));

$activeCutoff = 60 * 6;

$this->Table->reset();
foreach ($slowQueries as $slowQuery) {
	$processId = substr($slowQuery['SlowQuery']['id'], 8);
	$active = (time() - strtotime($slowQuery['SlowQuery']['modified'])) < $activeCutoff;

	$url = array(
		'controller' => 'slow_queries',
		'action' => 'delete',
		$slowQuery['SlowQuery']['id'],
	);
	$this->Table->cell($processId, 'Process', 'id');
	
	$cells = array(
		'user',
		'host',
		'db',
		'command',
		'time',
		'state',
		'query',
		'created',
		'modified',
	);
	foreach ($cells as $field) {
		$cell = $slowQuery['SlowQuery'][$field];
		if ($field == 'created' || $field == 'modified') {
			$cell = $this->Calendar->niceShort($cell);
		} else if ($field == 'id' && $active) {
			$cell = $this->Html->link($cell, array('action' => 'kill_process', $processId));
		}
		
		if ($active) {
			$cell = $this->Html->tag('strong', $cell);
		}
		
		$this->Table->cell($cell, Inflector::humanize($field), $field);
	}
	$this->Table->cell(
		$this->ModelView->actionMenu(array(
			$this->Html->link(
				$this->Html->image('icn/16x16/delete.png'),
				array(
					'action' => 'delete_slow_query',
					$slowQuery['SlowQuery']['id']
				),
				array('escape' => false)
			)
		)), 
		'Actions'
	);
	$this->Table->rowEnd();
}
echo $this->Table->output(array('paginate' => true));