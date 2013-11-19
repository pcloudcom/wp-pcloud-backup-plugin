var unlink_account;
var getBackupsFromPcloud;
var makeBackupNow;

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
	makeBackupNow = function(el){
		el.text("Backup is started").attr('disabled',true).attr('onclick','return false');
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
//		div.html('Loading your backups data');
		$.getJSON(api_url+"listfolder?path=/"+php_data.PCLOUD_BACKUP_DIR+"&auth="+php_data.pcloud_auth,function(data){
			if(data.result != "0") {
				if(data.result == "2005") {
					$.getJSON(api_url+"createfolder",{'auth':php_data.pcloud_auth,'path':'/'+php_data.PCLOUD_BACKUP_DIR,'name':php_data.PCLOUD_BACKUP_DIR},function(data){
						getBackupsFromPcloud();
					});
				}
			}else {
				html = "";
				html = html + '<ul>';
				$.each(data.metadata.contents,function(k,el){
					if( el.contenttype != "application/zip" ) { return true; }
					html = html +'<li><a target="blank_" href="https://my.pcloud.com/#folder='+data.metadata.folderid+'&page=filemanager&authtoken='+php_data.pcloud_auth+'"><img src="https://my.pcloud.com/img/icons/20/archive.png" alt="" /> '+el.name+' </a></li>';
				});
				html = html + '</ul>';
				div.html(html);
			}
		});
		setTimeout(getBackupsFromPcloud, 10000);
	};
	
	getBackupsFromPcloud();
	
});