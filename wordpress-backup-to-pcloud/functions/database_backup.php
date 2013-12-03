<?php

class wp2pcloudDatabaseBackup {
	
	private $tables,$db,$save_file,$write_file,$max_data_limit;
	
	public function __construct(){
		global $wpdb;
		$this->tables = array();
		$this->db = $wpdb;
		$this->save_file = 'php://temp';
		$this->save_file = tempnam(sys_get_temp_dir(), 'sqlarchive');
		$this->write_file = fopen($this->save_file,'r+');
		$this->max_data_limit = 20;
	}

	public function start(){
		if(self::test_mysqldump() == true) { 
			return $this->save_file; 
		} else {
			self::get_tables();
			return self::start_sql_backup();
		}
	}
	
	private function get_tables(){
		$sql = "SHOW TABLES";
		$r = $this->db->get_results($sql,ARRAY_N);
		foreach($r as $el) {
			$this->tables[] = $el[0];
		}
	}
	
	private function start_sql_backup() {
		$blog_time = strtotime(current_time('mysql'));
		$this->write("/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
		$this->write("/*!40101 SET NAMES utf8 */;\n");
		$this->write("/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n");
		$this->write("/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n\n");
		$this->write('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . "\n\n");
		foreach ($this->tables as $table) {
			$res = self::tableStracture($table);
			$this->write($res);
			self::backUpTable($table);
		}
		$this->write("/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */; \n");
		$this->write("/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;\n");
		$this->write("/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */\n\n");

		return $this->save_file;
	}
	
	private function test_mysqldump(){
		$cmd = "mysqldump -h".DB_HOST." -u ".DB_USER." --password=".DB_PASSWORD." --skip-comments ".DB_NAME." > ".$this->save_file;
		exec($cmd,$out);
		if(file_exists($this->save_file) && filesize($this->save_file) != 0) {
			return true;
		}
		return false;
	}
	
	
	private function backUpTable($table,$offset = 0){
		$row_count = 0;
		$broy = $this->db->get_var("SELECT COUNT(*) FROM $table");	// yeah it's bad but ...
		if ($broy == 0) {
			
		} else {
			for ($i = $offset; $i < $broy; $i = $i + $this->max_data_limit) {
				$sql = "SELECT * FROM $table LIMIT " . $this->max_data_limit . " OFFSET ".$i;
				$table_data = $this->db->get_results($sql,ARRAY_A);
				foreach ($table_data as $k => $v) {
					$this->write( self::mysql_insert_array($table,$v). ";\n" );										
				}
			}
		}
	}
	
	private function mysql_insert_array($table, $data,$delayed = false,$ignore = false) {
		global $wpdb;
		foreach ($data as $field=>$value) {  $fields[] = '`' . $field . '`';  $values[] = "'" . $wpdb->escape($value) . "'";  }
		$field_list = join ( ',', $fields );
		$value_list = join ( ', ', $values );
		$d = ($delayed == true) ? ' DELAYED ' : '';
		$ig = ($ignore == true) ? ' IGNORE ' : '';
		$query = "INSERT ".$d." ".$ig." INTO `" . $table . "` (" . $field_list . ") VALUES (" . $value_list . ")";
		return $query;
	}
	private function tableStracture($table) {
		$return = "\nDROP TABLE IF EXISTS `{$table}`;\n\n";
		$row = ( mysql_fetch_row(mysql_query("SHOW CREATE TABLE {$table}")) );
		$return .= $row[1].";\n\n";
		return $return;
	}
	private function write($string){
		fputs($this->write_file, $string);
	}
}