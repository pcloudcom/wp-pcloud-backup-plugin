<?php 
$auth = wp2pcloud_getAuth();

$days = array(
		'1'=>'Sunday',
		'Monday',
		'Tuesday',
		'Wednesday',
		'Thursday',
		'Friday',
		'Saturday',
);
$freg = array (
		'daily' => array (
				'interval' => 86400,
				'display' => 'Daily' 
		),
		'weekly' => array (
				'interval' => 604800,
				'display' => 'Weekly' 
		),
		'fortnightly' => array (
				'interval' => 1209600,
				'display' => 'Fortnightly' 
		),
		'monthly' => array (
				'interval' => 2419200,
				'display' => 'Once Every 4 weeks' 
		) 
);
$sch_data = wp2pcloud_getSchData();
?>
<div class="wrap">
	<h2><?php _e('WordPress Backup to Pcloud', 'wp2pcloud'); ?></h2>
	<p class="description"><?php printf( 'Version %s', BACKUP_TO_PCLOUD_VERSION) ?></p>
</div>


<?php  if($auth == false) {	 ?>
<form action="" id="link_pcloud_form">
	<table>
		<tbody>
			<tr>
				<td>Username:</td>
				<td><input placeholder="<?php echo __('Your pCloud username','wp2pcloud')?>" type="text" name="username" /></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type="password" placeholder="<?php echo __('Your pCloud password','wp2cloud')?>" name="password" /></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" name="submit" value="<?php echo __('Link with your pCloud account','wp2pcloud')?>" class="button-secondary" /></td>
			</tr>
		</tbody>
	</table>
</form>
<?php }else { ?>

<h3><?php echo __('Your account is linked with pCloud','wp2pcloud')?></h3>
<div id="pcloud_info"></div>
<a href="#" onclick="unlink_account(jQuery(this));;return false;" class="button-secondary"><?php echo __('unlink your account','wp2pcloud')?></a>

<div style="margin-top: 20px;">
	<h3><?php echo __("List of your previus backups",'wp2pcloud')?></h3>
	<div id="pcloudListBackups"></div>
</div>


<div>
	<h3><?php echo __('Schedule settings','wp2pcloud');?></h3>

	<form action="" id="wp2pcloud_sch">
	
	<div id="setting-error-settings_updated" class="updated settings-error below-h2" style="margin:0px;margin-bottom:10px;display:none;"> 
		<p><?php echo __('Your settings are saved','wp2pcloud')?></p>
	</div>
	
		<table>
			<tbody>
				<tr>
					<td>Day and Time</td>
					<td><select name="day" id="wp2schday">
							<?php foreach($days as $k=>$v) {?>
							<option <?php if(isset($sch_data['day']) && $sch_data['day'] == $v ) { echo " selected='selected' "; }?> value="<?php echo $v?>"><?php echo $v?></option>
							<?php } ?>
						</select> at <select name="hour" id="wp2hour">
							<?php foreach(range(0, 24) as $k=>$v) {?>
							<option <?php if(isset($sch_data['hour']) && $sch_data['hour'] == $v ) { echo " selected='selected' "; }?> value="<?php echo $v?>"><?php echo str_pad($v,2,'0',STR_PAD_LEFT)?>:00</option>
							<?php } ?>
						</select></td>
				</tr>
				<tr>
					<td>Frequency</td>
					<td><select name="freq" id="freq">
							<?php foreach($freg as $k => $el) { ?>
								<option <?php if(isset($sch_data['freq']) && $sch_data['freq'] == $k ) { echo " selected='selected' "; }?> value="<?php echo $k?>"><?php echo $el['display']?></option>
							<?php  }?>
						</select></td>
				</tr>
			</tbody>
		</table>
		<input type="submit" name="submit" value="<?php echo __('Save settings','wp2pcloud')?>" class="button-primary" />
	</form>
</div>

<div style="margin-top: 40px;">
	<a href="#" class="button" onclick="makeBackupNow(jQuery(this));return false;">Make backup now</a>
</div>

<?php } ?>