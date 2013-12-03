var unlink_account;
var getBackupsFromPcloud;
var makeBackupNow;
var restore_file;

jQuery(function($){

	var api_url = 'https://api.pcloud.com/';
	var ajax_url = 'admin-ajax.php?action=wp2pclod';

	$('#link_pcloud_form').submit(function(e){
		e.preventDefault();
		$.getJSON(api_url + 'userinfo?getauth=1&logout=1&username='+$('#link_pcloud_form [name="username"]').val()+"&password="+$('#link_pcloud_form [name="password"]').val(),{},function(data){
			if(data.result != "0") {
				alert(data.error);
			}else {
				$.post(ajax_url+'&method=set_auth',{'auth':data.auth},function(data){
					if(data.status == '1') {
						window.location.reload();
					}
				},'JSON');
			}
		});
	});
	
	$('#wp2pcloud_sch').submit(function(e){
		e.preventDefault();
		$('#setting-error-settings_updated').show();
		$.post(ajax_url+'&method=set_schedule',$(this).serialize(),function(data){
			$.get('admin.php');
		},'JSON');
	});
	
	restore_file = function(id) {
		if(confirm("Are you sure?")) {
			$('#wp2pcloud_restoring').show();
			$('#wp2pcloud_settings').hide();
			$.getJSON(ajax_url+"&method=check_can_restore",function(data){
				if(data.status == "0") {
					$.post(ajax_url+'&method=restore_archive',{'file_id':id},function(data){
						window.location = window.location+"&msg=restore_ok";
					});		
				}else { // show error
					$('#message').html(data.msg);
				}
			});
		}
	};
	
	makeBackupNow = function(el){
		el.text("Backup is started").attr('disabled',true).addClass('disabled').attr('id','_setDisabled_btn').attr('onclick','return false');
		$.post(ajax_url+'&method=start_backup',{},function(data){
			$.get('admin.php');
		},'JSON');
	};
	unlink_account = function(el){
		$.post(ajax_url+'&method=unlink_acc',function(data){
			window.location.reload();
		});
	};
	
	if($('#pcloud_info').length != 0) {
		$.getJSON(api_url+"userinfo?auth="+php_data.pcloud_auth,function(data){
			$('#pcloud_info').html('You have ' + humanFileSize(data.quota - data.usedquota) + " free space on pCloud <br /> <br />");
		});
	}
	
	
	function humanFileSize(bytes, si) {
	    var thresh = si ? 1000 : 1024;
	    if(bytes < thresh) return bytes + ' B';
	    var units = si ? ['kB','MB','GB','TB','PB','EB','ZB','YB'] : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
	    var u = -1;
	    do {
	        bytes /= thresh;
	        ++u;
	    } while(bytes >= thresh);
	    return bytes.toFixed(1)+' '+units[u];
	};
	
	
	getBackupsFromPcloud = function(){
		if( $('#pcloudListBackups').length == 0 ) { return false; }
		div = $('#pcloudListBackups');
		$.getJSON(api_url+"listfolder?path=/"+php_data.PCLOUD_BACKUP_DIR+"&auth="+php_data.pcloud_auth,function(data){
			if(data.result != "0") {
				if(data.result == "2005") {
					folders = php_data.PCLOUD_BACKUP_DIR.split("/");
					if(folders.length == 2) {
						$.getJSON(api_url+"createfolder",{'auth':php_data.pcloud_auth,'path':'/'+folders[0],'name':folders[0]},function(data){
							$.getJSON(api_url+"createfolder",{'auth':php_data.pcloud_auth,'path':'/'+folders[0]+"/"+folders[1],'name':folders[1]},function(data){
								getBackupsFromPcloud();
							});
						});
					}
				}
			} else {
				html = "";
				html = html + '<table> <tbody> ';
				$.each(data.metadata.contents,function(k,el){
					if( el.contenttype != "application/zip" ) { return true; }
					html = html +'<tr> <td> <a target="blank_" href="https://my.pcloud.com/#folder='+data.metadata.folderid+'&page=filemanager&authtoken='+php_data.pcloud_auth+'"><img src="'+php_data.archive_icon+'" alt="" /> '+el.name+' </a></td> <td><a file_id="'+el.fileid+'" onclick="restore_file('+el.fileid+');return false;" href="#" class="button">Restore</a></td> </tr> ';
				});
				html = html + '</tbody> </table>';
				
				if(div.html() != html) {
					if($('#_setDisabled_btn').length != 0) {
						$('#_setDisabled_btn').attr('onclick','makeBackupNow(jQuery(this));return false').attr('disabled',false).removeClass('disabled').text('Backup now');
					}
				}
				div.html(html);
			}
		});
		setTimeout(getBackupsFromPcloud, 30000);
	};
	
	getBackupsFromPcloud();
	
});