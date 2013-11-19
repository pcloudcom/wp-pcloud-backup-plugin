<?php
class wp2pcloudFilesBackup {
	private $save_file, $write_filename, $sql_backup_file;
	public function __construct() {
		$this->save_file = "archive.zip";
		$this->write_filename = tempnam ( sys_get_temp_dir (), 'archive' );
	}
	public function setArchiveName($name) {
		$this->save_file = ($name != "") ? $name : $this->save_file;
		$this->save_file = preg_replace ( '@(https://)|(http://)@', '', $this->save_file );
		$this->save_file = str_replace ( " ", "_", $this->save_file );
		$this->save_file = str_replace ( "/", "_", $this->save_file );
	}
	public function setMysqlBackUpFileName($name) {
		$this->sql_backup_file = $name;
	}
	public function start() {
		$dirs = self::find_all_files ( rtrim ( ABSPATH, '/' ) );
		self::create_zip ( $dirs );
		self::send_to_pcloud ();
	}
	private function find_all_files($dir) {
		$root = scandir ( $dir );
		foreach ( $root as $value ) {
			if ($value === '.' || $value === '..') {
				continue;
			}
			if (is_file ( "$dir/$value" )) {
				$result [] = "$dir/$value";
				continue;
			}
			foreach ( self::find_all_files ( "$dir/$value" ) as $value ) {
				$result [] = $value;
			}
		}
		return $result;
	}
	private function create_zip($files) {
		$zip = new ZipArchive ();
		$zip->open ( $this->write_filename, ZIPARCHIVE::CREATE );
		$zip->setArchiveComment ( "Wordpress2pClod" );
		foreach ( $files as $el ) {
			$zip->addFile ( $el );
		}
		if ($this->sql_backup_file != false) {
			$zip->addFile ( $this->sql_backup_file, 'backup.sql' );
		}
		$zip->close ();
		
		if ($this->sql_backup_file != false) {
			unlink ( $this->sql_backup_file );
		}
	}
	private function makeDirectory() {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)" );
		curl_setopt ( $ch, CURLOPT_URL, 'http://api.pcloud.com/createfolder?path=/WORDPRESS_BACKUPS&name=WORDPRESS_BACKUPS&auth=' . wp2pcloud_getAuth () );
		$response = curl_exec ( $ch );
		$response = @json_decode ( $response );
		curl_close ( $ch );
		return $response;
	}
	private function getUploadDirId() {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)" );
		curl_setopt ( $ch, CURLOPT_URL, 'http://api.pcloud.com/listfolder?path=/WORDPRESS_BACKUPS&auth=' . wp2pcloud_getAuth () );
		$response = curl_exec ( $ch );
		$response = @json_decode ( $response );
		curl_close ( $ch );
		$folder_id = false;
		if ($response->result == 2005) {
			$res = self::makeDirectory ();
			$folder_id = $res->metadata->folderid;
		} else {
			$folder_id = $response->metadata->folderid;
		}
		return $folder_id;
	}
	private function send_to_pcloud() {
		if (! file_exists ( $this->write_filename )) {
			echo "File don't exist";
			return false;
		}
		$folder_id = self::getUploadDirId ();
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)" );
		curl_setopt ( $ch, CURLOPT_URL, 'http://api.pcloud.com/uploadfile?folderid=' . $folder_id . '&auth=' . wp2pcloud_getAuth () );
		$data = array (
				'uploaded_file' => '@' . $this->write_filename . ';filename=' . $this->save_file 
		);
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		$response = curl_exec ( $ch );
		unlink ( $this->write_filename );
		return $response;
	}
}