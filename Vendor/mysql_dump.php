<?php
require_once "ftp.php";
require_once "log_file.php";

class MysqlDump {
	var $mysqlLogin;
	var $mysqlPass;
	var $mysqlDb;
	var $hasLogin = false;
	
	var $gzip = true;
	
	var $dumpDir = '/home/souper/tmp/mysql_backup/';
	
	var $dumpFile;
	var $dumpFilename;
	
	var $useLogFile = true;
	var $logDir = null;		//Will default to dumpDir
	var $logFile = 'mysql_dump_log';
	
	var $hasFtp = false;
	var $Ftp;
	var $ftp = array(
		'server',
		'userName',
		'password',
		'port' => 21,

		'dir',
	);
	var $ftpMaxBackups = 7;
	
	var $LogFile;
	
	function __construct($login = null, $pass = null, $db = null, $ftp = null, $dir = null) {
		set_time_limit(7200);

		$this->setLog();
		
		$this->setLogin($login, $pass, $db);
		
		if (!empty($dir)) {
			$this->dumpDir = $dir;
		}
		try {
			$this->setFtp($ftp);
			if ($this->hasLogin) {
				$this->run();
			}
		} catch (Exception $e) {
			exit('MysqlDump Error: ' . $e->getMessage());
		}
	}
	
	function setLogin($login = null, $pass = null, $db = null) {
		$vars = array('login', 'pass', 'db');
		$hasLogin = true;
		foreach ($vars as $var) {
			if (!empty($$var)) {
				$this->{'mysql' . ucfirst($var)} = $$var;
			} else {
				$hasLogin = false;
			}
		}
		$this->hasLogin = $hasLogin;
		
		if ($hasLogin) {
			$this->log('MysqlDump Initialized for database: ' . $db);
		}
		return $hasLogin;
	}
	
	function run($login = null, $pass = null, $db = null) {
		$this->setLogin($login, $pass, $db);
		
		$this->log('Beginning MysqlDump', 'COMPLETE');
		$this->log('Creating file: ' . $this->dumpFile);
		if ($cmd = $this->getMysqlDumpCmd()) {
			exec($cmd);
			$this->log('mysqldump command executed');
			$this->log($cmd);
			$this->setFtpBackup();
			$success = true;
		} else {
			$this->error('There was an error with MysqlDump');
			$success = false;
		}
		$this->log('MysqlDump Completed', 'COMPLETE');
		return $success;
	}
	
	function setDumpDir($dir = null) {
		if (!empty($dir)) {
			$this->dumpDir = $dir;
		}
		if (!is_dir($this->dumpDir) && !mkdir($this->dumpDir)) {
			return error("{$this->dumpDir} is not a directory. Cannot continue");
		}
		$this->setLog();	//refreshes Log Directory
		return true;
	}
	
	function getMysqlDumpCmd() {
		$this->setDumpFile();
		$cmd = sprintf('mysqldump -u %s -p%s %s ',
			$this->mysqlLogin,
			$this->mysqlPass,
			$this->mysqlDb
		);
		if ($this->gzip) {
			$cmd .= '| gzip ';
		}
		$cmd .= '> ' . $this->dumpFile;
		return $cmd;		
	}

	function setDumpFile() {
		$this->dumpFilename = $this->mysqlDb . date('YmdHis') . '.sql';
		if ($this->gzip) {
			$this->dumpFilename .= '.gz';
		}
		$this->dumpFile = $this->dumpDir . $this->dumpFilename;
		return $this->dumpFile;		
	}

	//FTP
	function connectFtp($ftp = null) {
		$this->setFtp($ftp);	
		if (!empty($this->ftp)) {
			if (empty($this->Ftp)) {
				$this->Ftp = new Ftp($this->ftp);
				if (!($this->Ftp->setDir($this->ftp['dir']))) {
					return $this->error('Could not set FTP directory to "' . $this->ftp['dir'] . '"');
				}
				$this->Ftp->setLogFile($this->logDir, $this->logFile);
				$this->hasFtp = true;
				return true;
			} else {
				return $this->Ftp->reconnect($this->ftp);
			}
		}
		return null;
	}

	function setFtp($ftp = null) {
		if (!empty($ftp)) {
			$this->ftp = array_merge($this->ftp, $ftp);
		}
	}
	

	function setFtpBackup() {
		$flag = 'FTP';
		if (!$this->connectFtp()) {
			$this->log('No FTP info. Skipping');
			return null;
		}
		
		$this->log('Beginning FTP backup', $flag);
		if (!is_file($this->dumpFile)) {
			return $this->error(sprintf('%s is not a file. Cannot upload', $this->dumpFile), $flag);
		}
		if ($this->Ftp->upload($this->dumpFile, $this->ftp['dir'] . $this->dumpFilename)) {
			$this->log('FTP backup completed successfully', $flag);
			unlink($this->dumpFile);
			$this->removeOldFtpBackups();
			return true;
		} else {
			return $this->error('There was an error uploading file to FTP', $flag);
		}
	}

	function removeOldFtpBackups($dir = null, $maxBackups = null) {
		if (empty($dir)) {
			$dir = $this->ftp['dir'];
		}
		$this->log('Cleaning up FTP directory: ' . $dir, 'FTP_CLEANUP');
		if (empty($maxBackups) && !empty($this->ftpMaxBackups)) {
			$maxBackups = $this->ftpMaxBackups;
		}
		if (empty($maxBackups)) {
			return null;
		}
		
		if (!empty($this->ftpMaxBackups)) {
			list($backups, $keep, $delete) = array(array(), array(), array());
			
			$files = $this->Ftp->getDirList($dir);
			if (!empty($files)) {
				foreach ($files as $file) {
					if (preg_match('/([A-Za-z_\-]+)([\d]{14}).sql/', $file['name'], $match)) {
						$backups[$match[1]][$match[2]] = $dir . $file['name'];
					}
				}
				if (!empty($backups)) {
					foreach ($backups as $db => $files) {
						krsort($files);
						$files = array_values($files);
						foreach ($files as $k => $file) {
							if ($k >= $maxBackups) {
								$delete[$db][] = $file;
							} else {
								$keep[$db][] = $file;
							}
						}
					}
					if (!empty($delete)) {
						foreach ($delete as $db => $paths) {
							$this->log('Removing ' . count($paths) . ' backups for DB: ' . $db . ' (Keeping ' . count($keep[$db]) . ')');
							foreach ($paths as $path) {
								$this->Ftp->delete($path);
							}
						}
					}
				}
			}
		}
		$this->log('Finished cleaning up FTP directory: ' . $dir, 'FTP_CLEANUP');
	}

	private function error($msg, $timeFlag = null) {
		$this->log('Error: ' . $msg, $timeFlag);
		throw new Exception('MysqlDump Error: ' . $msg);
		return false;
	}
	
	private function log($msg, $timeFlag = null) {
		$this->log[] = date('YmdHis') . ': ' . $msg;
		$this->LogFile->write($msg, $timeFlag);
	}
	
	private function setLog() {
		if ($this->useLogFile) {
			if (empty($this->logDir)) {
				$this->logDir = $this->dumpDir;
			}
		}
		$this->LogFile = new LogFile($this->logDir, $this->logFile);
	}
	
}