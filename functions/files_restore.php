<?php
class wp2pcloudFilesRestore {

	private $write_filename,$auth,$restore_path,$file_id;
	
	public function __construct() {
		$this->restore_path = ABSPATH;
// 		$this->restore_path = '/tmp/test/';
		$this->write_filename = tempnam ( sys_get_temp_dir (), 'restore_archive' );
		error_reporting(-1);
	}

	public function setAuth($key){
		$this->auth = $key;
	}
	public function setFileId($id){
		$this->file_id = $id;
	}
	public function getFile(){
		$url = 'https://api.pcloud.com/getfilelink?fileid='.$this->file_id.'&auth='.$this->auth;
		$r = file_get_contents($url);
		$r = json_decode($r);
		if($r->result == "0") {
			$url = "http://".reset($r->hosts).$r->path;
		}
		self::copyfile_chunked($url,$this->write_filename);
		self::extract();
		self::restoreDB();
		self::removeFiles();
	}
	private function removeFiles(){
		@unlink($this->write_filename);
	}
	private function restoreDB(){
		$sql = $this->restore_path."backup.sql";
		if(! is_file($sql)) { return  false; }
		global $wpdb;
		$q = explode(";\n",file_get_contents($sql));
		foreach($q as $k => $v) {
			$v = trim($v);
			if($v == "") continue;
			$wpdb->query($wpdb->prepare( $v ) );
		}
		unlink($sql);
	}
	private function extract(){
		$zip = new ZipArchive;
		$res = $zip->open($this->write_filename);
		if ($res === TRUE) {
			for($i = 0; $i < $zip->numFiles; $i++) {
				$zip->extractTo($this->restore_path, array($zip->getNameIndex($i)));
			}
		} else {
			echo 'failed';
		}
	}
	
	private function copyfile_chunked($infile, $outfile) {
		$chunksize = 2 * (1024 * 1024); // 2 Megs
		$parts = parse_url($infile);
		$i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
		$o_handle = fopen($outfile, 'wb');
		if ($i_handle == false || $o_handle == false) {
			return false;
		}
		if (!empty($parts['query'])) {
			$parts['path'] .= '?' . $parts['query'];
		}
		$request = "GET {$parts['path']} HTTP/1.1\r\n";
		$request .= "Host: {$parts['host']}\r\n";
		$request .= "User-Agent: Mozilla/5.0\r\n";
		$request .= "Keep-Alive: 115\r\n";
		$request .= "Connection: keep-alive\r\n\r\n";
		fwrite($i_handle, $request);
		$headers = array();
		while(!feof($i_handle)) {
			$line = fgets($i_handle);
			if ($line == "\r\n") break;
			$headers[] = $line;
		}
		$length = 0;
		foreach($headers as $header) {
			if (stripos($header, 'Content-Length:') === 0) {
				$length = (int)str_replace('Content-Length: ', '', $header);
				break;
			}
		}
		$cnt = 0;
		while(!feof($i_handle)) {
			$buf = '';
			$buf = fread($i_handle, $chunksize);
			$bytes = fwrite($o_handle, $buf);
			if ($bytes == false) {
				return false;
			}
			$cnt += $bytes;
			if ($cnt >= $length) break;
		}
		fclose($i_handle);
		fclose($o_handle);
		return $cnt;
	}
}