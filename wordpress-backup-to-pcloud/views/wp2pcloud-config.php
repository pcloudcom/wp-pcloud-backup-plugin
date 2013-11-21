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
		'monthly' => array (
				'interval' => 2419200,
				'display' => 'Montly' 
		) 
);
$sch_data = wp2pcloud_getSchData();
$next_sch = wp_next_scheduled('run_pcloud_backup_hook');
$imgUrl = rtrim ( WP_PLUGIN_URL, '/' ) . '/' . PCLOUD_DIR."/images/";
?>
<div id="wp2pcloud">

	<div class="top">
		<div class="logo">
			<a href="//pcloud.com" target="_blank"><img src="<?php echo $imgUrl?>/logo-n1.png" alt="" /></a>
		</div>

		<div class="desc">
			<h2><?php _e('WordPress Backup to Pcloud', 'wp2pcloud'); ?></h2>
			<p class="description"><?php printf( 'Version %s', BACKUP_TO_PCLOUD_VERSION) ?></p>
		</div>
		<div class="clear"></div>
	</div>
	
	<?php if(isset($_GET['msg']) && $_GET['msg'] == 'restore_ok') {?>
		<div id="message" class="updated below-h2">
		<p><?php echo __('Your files and database has been restored successfull')?> </p>
	</div>
	<?php } ?>
	
	<div id="wp2pcloud_restoring" style="display: none;">
		<h3><?php echo __('Restoring from archive','wp2pcloud');?></h3>

		<div style="text-align: center;">
			<div id="message" class="updated below-h2">
				<p><?php echo __('Please wait, your backup is downloading')?> <img src="<?php echo  rtrim ( WP_PLUGIN_URL, '/' ) . '/' . PCLOUD_DIR . '/images/preload.gif'?>" alt="" /> <br /> <br />
				</p>
			</div>

			<div style="text-align: left; margin-top: 10px;">
				<?php echo __("When your backup is restored, this page will reload!",'wp2pcloud')?>
			</div>
		</div>

	</div>

	<div id="wp2pcloud_settings" class="<?php echo ($auth == false) ? 'login_page' :''; ?>" >
	<?php  if($auth == false) {	 ?>
	
	<div>
	
		<div style="background:url('<?php echo $imgUrl?>/login_bcgr.png') no-repeat;width:954px;height: 499px;">
			<form style="padding-top: 128px;padding-left: 105px;" action="" id="link_pcloud_form">
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
				<div class="register_btn"><a target="_blank" href="//my.pcloud.com/#page=register&ref=26"><img src="<?php echo $imgUrl?>/register_btn.png" alt="" /></a></div>
		</div>
	
		<div class="clear"></div>
	</div>
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

				<div id="setting-error-settings_updated" class="updated settings-error below-h2" style="margin: 0px; margin-bottom: 10px; display: none;">
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
	</div>
</div>


<script>
	<?php if($auth != false){?>
		jQuery(function($){
			$('#wpwrap').css({'background':'url("<?php echo $imgUrl?>/bckgr_1.png") no-repeat bottom right'});
		});
	<?php }?>
</script>
