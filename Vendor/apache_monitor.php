<?php
class ApacheMonitor {
	
	//TOP
	var $topCpuLine = 2;
	var $topMemoryLine = 3;
	
	var $topCpuKeys = array(
		'id'=>'Idle',
		'us'=>'User Mode',
		'sy'=>'System Mode',
		'ni'=>'Nice Tasks',
		'wa'=>'Wait',
		'hi'=>'hi',
		'si'=>'si',
		'st'=>'st'
	);

	var $processColumns = array('pid', 'user', 'pr', 'ni', 'virt', 'res', 'shr', 's', 'cpu', 'mem', 'time', 'command');
	
	//HTTPD Status
	var $apacheStatusKeys = array(
		"_" => 'Waiting for Connection', 
		"S" => 'Starting up', 
		"R" => 'Reading Request',
		"W" => 'Sending Reply', 
		"K" => 'Keepalive (read)', 
		"D" => 'DNS Lookup',
		"C" => 'Closing connection', 
		"L" => 'Logging', 
		"G" => 'Gracefully finishing',
		"I" => 'Idle cleanup of worker', 
		"." => 'Open slot with no current process'
	);

	var $suffixes = array('','b','K','M','G','T');

	function __construct() {
	
	}
	
	function getTop() {
		//Parses TOP info
		list($output,$output1)=array(array(),array());
		$iterations = 2;
		
		//Calls Apache command
		exec('/usr/bin/top -b -n'.$iterations.' -d1', $output1, $return);
		$match = 0;
		//Runs 2 iterations, since the first iteration always appears to return the same CPU and Memory Values
		//Finds the second iteration:
		foreach($output1 as $line) {
			if(strstr($line,'top - ')) {
				$match++;
			}
			if($match >= $iterations) {
				$output[] = $line;
			}
		}
		return $output;
	}
	
	public function killOldProcesses($threshold = null) {
		$processes = $this->getOldProcesses($threshold);
		$killCount = count($processes);
		$this->kill(array_keys($processes));
		return $killCount;
	}
	
	public function kill($pid) {
		if (is_array($pid)) {
			foreach ($pid as $subPid) {
				$this->kill($subPid);
			}
		} else {
			exec('kill -9 ' . $pid);
		}
	}
	
	public function getOldProcesses($threshold = null) {
		if (empty($threshold)) {
			$threshold = 3600;
		}
		$processes = $this->getProcesses();
		$return = array();
		foreach ($processes as $pid => $process) {
			if ($process['seconds'] > $threshold) {
				$return[$pid] = $process;
			}
		}
		return $return;
	}
	
	function getMemInfo() {
		$startTime = $this->_timeTrack();
		$total = array();
		$detail = array(
			'MemTotal' => 0,
			'MemFree' => 0,
			'Cached' => 0,
		);
		exec('cat /proc/meminfo', $output);
		foreach ($output as $line) {
			if (preg_match('/([^:]+):[^\d]+([\d]+)[\s]*([A-Za-z]*)/', $line, $matches)) {
				$val = $matches[2];
				if ($matches[3] == 'kB') {
					$val *= 1000;
				}
				$detail[$matches[1]] = $val;
			}
		}
		$total = array(
			'used' => $detail['MemTotal'] - $detail['MemFree'] - $detail['Cached'],
			'free' => $detail['MemFree'] + $detail['Cached'],
			'total' => $detail['MemTotal'],
			'time' => $this->_timeTrack($startTime)
		);
		return compact('total','detail');
	}
	
	function getProcesses() {
		$top = $this->getTop();
		$processStartLine = 7;
		$lines = array_slice($top, $processStartLine);
		$processes = array();
		$columnCount = count($this->processColumns);
		foreach($lines as $line) {
			preg_match_all('/[\s]*([^\s]+)/', $line, $matches);
			if (!empty($matches[1]) && count($matches[1]) == $columnCount) {
				$pid = $matches[1][0];
				$command = $matches[1][11];
				if ($command == 'php') {
					foreach ($this->processColumns as $k => $col) {
						$processes[$pid][$col] = $matches[1][$k];
					}
					$processes[$pid]['seconds'] = $this->_timeToSeconds($processes[$pid]['time']);
				}
			}
		}
		return $processes;
	}
	
	function getTopStatus() {
		$startTime = $this->_timeTrack();
		$output = $this->getTop();
		
		$return = array(
			'cpu' => $this->_topCpu($output),
			'memory' => $this->_topMemory($output),
		);
		
		$return['time'] = $this->_timeTrack($startTime);
		
		return $return;
		
		//Displays statistics about the top command
		//$body->add_child('p',substr($output[0],6,8));
		//$body->add_child('p',$output[$this->cpuLine]);

	}
	
	function getApacheStatus() {
		//HTTPd status
		$match = array();
		$results = array();
		$startTime = $this->_timeTrack();
		
		exec('/etc/init.d/httpd status', $output, $return);
		
		$chkValid = str_replace('.','\.',implode(array_keys($this->apacheStatusKeys)));
		
		$return = array(
			'total' => 0,
			'used' => 0,
			'unused' => 0,
			'breakdown' => array(),
		);
		
		foreach($output as $line) {
			$line = trim($line);
			if(preg_match('/^['.$chkValid.']+$/',$line)) {
				preg_match_all('/['.$chkValid.']{0,1}/',$line,$hits);
				foreach($hits[0] as $m) {
					$return['total']++;
					if($m == '_') {
						$return['unused']++;
					} else if($m != '.') {
						$return['used']++;
					}
					if (empty($return['breakdown'][$m])) {
						$return['breakdown'][$m] = 0;
					}
					$return['breakdown'][$m]++;
				}
			}
		}
		$return['time'] = $this->_timeTrack($startTime);
		return $return;
	}

	public function getApacheStatusKeys() {
		return $this->apacheStatusKeys;
	}
	
	public function getCpuKeys() {
		return $this->topCpuKeys;
	}
	
	//Extracts Memory Usage from Linux Top output
	private function _topMemory($output) {
		$startTime = $this->_timeTrack();
		$return = array(
			'total' => 0,
			'used' => 0,
			'unused' => 0,
			'breakdown' => array()
		);
		if (!empty($output) && preg_match_all('/([\d\.]+[a-z]{0,1}) ([a-z]+)/', $output[$this->topMemoryLine], $matches)) {
			foreach($matches[1] as $matchKey =>$matchValue) {
				$key = $matches[2][$matchKey];
				$matchValue = $this->_stripSuffix($matchValue) / 1000;
				if($key == 'total') {
					$return['total'] = $matchValue;
					continue;
				} elseif($key == 'free') {
					$return['unused'] = $matchValue;
				} else {
					$return['used'] += $matchValue;
				}
				$return['breakdown'][$key] = $matchValue;
			}
		}
		$return['time'] = $this->_timeTrack($startTime);
		return $return;
	}
	
	//Extracts CPU Usage from Linux Top output
	private function _topCpu($output) {
		$startTime = $this->_timeTrack();
		$return = array(
			'total' => 0,
			'used' => 0,
			'unused' => 0,
			'breakdown' => array()
		);
		if (!empty($output) && preg_match_all('/([\d\.]*)%([a-z]{2})/', $output[$this->topCpuLine], $matches)) {
			foreach($matches[1] as $k=>$pct) {
				$key = $matches[2][$k];
				$return['total'] += $pct;
				$return['breakdown'][$key] = $pct;
				if($key=='id') {
					$return['unused'] += $pct;
				} else {
					$return['used'] += $pct;
				}
			}
		}
		$return['time'] = $this->_timeTrack($startTime);
		return $return;
	}
	
	private function _stripSuffix($number) {
		foreach($this->suffixes as $pow=>$suffix) {
			if($suffix != '' && strtolower(substr($number,-1))==strtolower($suffix)) {
				return $number * pow(1024,$pow);
			}
		}
		return $number;
	}
	
	private function _timeTrack($start = 0) {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		return $time - $start;
	}
	
	private function _timeToSeconds($time) {
		preg_match('/([\d]+):([\d]+).([\d]+)/', $time, $matches);
		return $matches[1] * 3600 + $matches[2] * 60 + $matches[3];
	}
}