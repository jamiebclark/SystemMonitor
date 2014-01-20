<?php
App::import('Vendor', 'SystemMonitor.ApacheMonitor');
class SystemMonitorHelper extends AppHelper {
	public $name = 'SystemMonitor';
	public $helpers = array(
		'Html',
		'Layout.TextGraph',
		'Layout.Table',
		'Number'
	);
	
	private $statusKeys;
	private $cpuKeys;
	
	private $ApacheMonitor;
	
	public function beforeRender($viewFile) {
		$this->ApacheMonitor = new ApacheMonitor();
		$this->statusKeys = $this->ApacheMonitor->getApacheStatusKeys();
		$this->cpuKeys = $this->ApacheMonitor->getCpuKeys();
		
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
		if ($time) {
			$out .= $this->Html->tag('p', sprintf('Loaded in %.2f seconds', $time));
		}
		return $this->Html->div('apachestatus-box', $out);
	}
	
	public function memoryDetail($memoryDetail) {
		$this->Table->reset();
		foreach(memoryDetail as $label => $val) {
			$reverse = ($label != 'free');
			$color = $this->TextGraph->colorRange($val, 0, $total, $reverse);
			$this->Table->cells(array(
				array($this->Number->toReadableSize($val),array('style'=>'color:'.$color)),
				array($label, array('style'=>'color:'.$color))
			), true);
		}
		return $this->Table->output();
	}
	
	public function cpuDetail($cpuDetail) {
		$this->Table->reset();
		foreach($this->cpuKeys as $key => $label) {
			$reverse = ($key != 'id');
			$color = $this->TextGraph->colorRange($cpuDetail[$key], 0, 100, $reverse);
			$this->Table->cells(array(
				array($cpuDetail[$key] . '%', array('style'=>'color:'.$color)),
				array($label, array('style'=>'color:'.$color))
			), true);
		}
		return $this->Table->output();
	}
	
	public function apacheDetail($apacheDetail) {
		$this->Table->reset();
		foreach ($apacheStatusKeys as $key => $label) {
			$val = !empty($apacheDetail[$key]) ? $apacheDetail[$key] : 0;
			$this->Table->cells(array(
				array(number_format($val)),
				array($key),
				array($label),
			), true);
			unset($apacheDetail[$key]);
		}
		foreach($apacheDetail as $key => $count) {
			$this->Table->cells(array(
				array(number_format($count)),
				array($key),
				array('&nbsp;'),
			), true);
		}
		return $this->Table->output();
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