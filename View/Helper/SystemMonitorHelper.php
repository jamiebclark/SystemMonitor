<?php
class SystemMonitorHelper extends AppHelper {
	public $name = 'SystemMonitor';
	public $helpers = array(
		'Html',
		'Layout.TextGraph',
		'Number'
	);
	
	public function beforeRender($viewFile) {
		$this->TextGraph->beforeRender($viewFile);
		return parent::beforeRender($viewFile);
	}
	
	public function statusBox($title, $used, $total, $type = null, $time = 0) {
		$unused = $total - $used;
		$color = $this->TextGraph->colorRange($unused, 0, $total);
		$out = '';
		if ($type != '%') {
			$title .= ' ' . $this->formatNumber($total, $type);
		}
		$out .= $this->Html->tag('h3', $title);
		$out .= $this->Html->tag('h4', sprintf('%s Used %s Idle', 
			$this->formatNumber($used, $type),
			$this->formatNumber($unused, $type)
		), array('style' => 'color: ' . $color));
		$out .= $this->TextGraph->barGraph($unused,$used);
		$out .= $this->Html->tag('p', sprintf('Loaded in %.2f seconds', $time));
		return $this->Html->div('apachestatus-box', $out);
	}
	
	private function formatNumber($number, $type = null) {
		if ($type == '%') {
			return number_format($number, 1) . '%';
		} else if ($type == 'size') {
			return $this->Number->toReadableSize($number);
		} else {
			return number_format($number, 1) . ' ' . $type;
		}
	}
	
}