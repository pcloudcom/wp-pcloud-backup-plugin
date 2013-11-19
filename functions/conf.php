<?php

function ydebug() {
	if (func_num_args() === 0)
		return;
	$params = func_get_args();
	$output = array();
	foreach ($params as $var){
		$output[] = '<pre>('.gettype($var).') '.print_r($var, TRUE).'</pre>';
	}
	return implode("\n", $output);
}

function wp2pcloud_getConfigValue($key) {
	global $wpdb;
	$sql = "SELECT `value` FROM `".$wpdb->prefix."wp2pcloud_config` WHERE `key` = '".$key."' LIMIT 1";
	$r = $wpdb->get_col($sql);
	if(empty($r)) {
		return false;
	}else {
		return $r[0];
	}
}

function wp2pcloud_getAuth() {
	return wp2pcloud_getConfigValue('auth');
}

function wp2pcloud_setAuth($key) {
	global $wpdb;
	$sql = "REPLACE INTO `".$wpdb->prefix."wp2pcloud_config` (`key`, `value`) VALUES ('auth', '".$key."')";
	return $wpdb->query($sql);
}
function wp2pclod_unlink(){
	global $wpdb;
	$sql = "DELETE FROM `".$wpdb->prefix."wp2pcloud_config` WHERE `key` = 'auth' LIMIT 1";
	$wpdb->query($sql);
}

function wp2pcloud_setSchData($data){
	global $wpdb;
	$data = json_encode($data);
	$sql = "REPLACE INTO `".$wpdb->prefix."wp2pcloud_config` (`key`, `value`) VALUES ('sch_data', '".$data."')";
	return $wpdb->query($sql);
}

function wp2pcloud_getSchData(){
	$data = wp2pcloud_getConfigValue('sch_data');
	if($data != false) {
		$data = json_decode($data,true);
	}
	return $data;
}