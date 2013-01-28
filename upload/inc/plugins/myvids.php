<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/* Hooks */

//User permissions
$plugins->add_hook("admin_formcontainer_end", "myvids_edit_group");
$plugins->add_hook("admin_user_groups_edit_commit", "myvids_edit_group_do");

$plugins->add_hook("member_profile_end", "myvids_prof");

/* End Hooks */

function myvids_info() {
    global $lang;
    
    $lang->load("myvids");
    
    return array(
        "name" => $lang->myvids_fulltitle,
        "description" => $lang->myvids_description,
        "website" => "http://mybbsource.com",
		"author" => "euantor",
        "authorsite" => "http://forrst.me/euantor",
        "version" => "1.0"
    );
    
}

function myvids_install() {
    global $mybb, $db, $cache;
    
    $db->write_query("CREATE TABLE  `".TABLE_PREFIX."videos` (
	`vid` INT NOT NULL AUTO_INCREMENT ,
	`uid` INT NOT NULL ,
	`vidname` VARCHAR( 200 ) NOT NULL ,
	`vidurl` VARCHAR( 200 ) NOT NULL ,
	`viddesc` VARCHAR( 1000 ) NOT NULL ,
	`viddate` DATETIME NOT NULL ,
	`vidmod` INT NOT NULL DEFAULT '0' ,		
	`vidcat` INT NOT NULL DEFAULT  '0' ,	
	`vidseolink` VARCHAR( 30 ) NOT NULL ,
	PRIMARY KEY (  `vid` )
	);");
    
    $db->write_query("CREATE TABLE  `".TABLE_PREFIX."videocomments` (
	`vcid` INT NOT NULL AUTO_INCREMENT ,
	`vid` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`vidcomment` VARCHAR( 1000 ) NOT NULL ,
	`commentdate` DATETIME NOT NULL ,
	PRIMARY KEY (  `vcid` )
	);");
    
    $db->write_query("CREATE TABLE  `".TABLE_PREFIX."videocategories` (
	`catid` INT NOT NULL AUTO_INCREMENT ,
	`catname` VARCHAR( 100 ) NOT NULL ,
	PRIMARY KEY (  `catid` )
	);");
    
    $db->insert_query("videocategories", array("catname" => 'Uncategorised'));
	
    // Creates fields for groups-based permissions
    $db->write_query("ALTER TABLE `".TABLE_PREFIX."usergroups` ADD `canusemyvids` INT(1) NOT NULL DEFAULT '0', ADD `canaddvids` INT(1) NOT NULL DEFAULT '0', ADD `cancommentvid` INT(1) NOT NULL DEFAULT '0', ADD `needvidmod` INT(1) NOT NULL DEFAULT '0', ADD `canmodvid` INT(1) NOT NULL DEFAULT '0';");
	
    // Set permissions
    $db->write_query('UPDATE '.TABLE_PREFIX.'usergroups SET canusemyvids = "1", canaddvids = "1"');
    $db->write_query('UPDATE '.TABLE_PREFIX.'usergroups SET cancommentvid = "1" WHERE gid IN (2, 3, 4, 6)');
	$db->write_query('UPDATE '.TABLE_PREFIX.'usergroups SET canmodvid = "1" WHERE gid IN (3, 4, 6)');
	
    $cache->update_usergroups();
}

function myvids_is_installed() {
    global $db;
	
    if($db->table_exists("videos") && $db->table_exists("videocomments") && $db->table_exists("videocategories"))
    {
    	return true;
    }
    else {
    	return false;
    }
}

function myvids_activate() {
    global $db, $mybb, $lang;
	
    $settings_group = array(
        "gid" => "",
        "name" => "myvids",
        "title" => "MyVids",
        "description" => "Setting group for the MyVids plugin.",
        "disporder" => "0",
        "isdefault" => "0",
    );
    
    $db->insert_query("settinggroups", $settings_group);
    $gid = $db->insert_id();
    
    $setting[1] = array(
	    "name" => "myvidson",
	    "title" => "Enable MyVids?",
	    "description" => "Set this to yes to turn on the MyVids system.",
	    "optionscode" => "yesno",
	    "value" => "1",
	    "disporder" => "1",
	    "gid" => $gid,
	);
	
    $setting[2] = array(
	    "name" => "myvidseo",
	    "title" => "Enable MyVids SEO URLs?",
	    "description" => "Do you wish to use \"SEO Firendly\" URLs in the video system? To use SEO Friendly URLs, you must make the edits to your .htacces file as outlined in the plugin documentation.",
	    "optionscode" => "yesno",
	    "value" => "0",
	    "disporder" => "2",
	    "gid" => $gid,
	);
	
    $setting[3] = array(
	    "name" => "myviduploading",
	    "title" => "Allow the uploading of video files?",
	    "description" => "Do you wish to allow users to upload their own files to your server? This can cause problems if you have a lot of users uploading a lot of videos and/or watching a lot of videos. Check the documentation for more info.",
	    "optionscode" => "yesno",
	    "value" => "0",
	    "disporder" => "3",
	    "gid" => $gid,
	);
	
    $setting[4] = array(
	    "name" => "myviduploaddir",
	    "title" => "Location of uplaoded files?",
	    "description" => "Where should uploaded video files be stored? Please follow the default\'s example of structure.",
	    "optionscode" => "text",
	    "value" => "./uploads/videos",
	    "disporder" => "4",
	    "gid" => $gid,
	);
	
    $setting[5] = array(
	    "name" => "myvidcommentminlength",
	    "title" => "Minimum comment length?",
	    "description" => "Minimum comment length is used in an attempt to deter spam. 10 is a good minimum value for msot boards.",
	    "optionscode" => "text",
	    "value" => "10",
	    "disporder" => "5",
	    "gid" => $gid,
	);
	
	foreach ($setting as $row) {
	    $db->insert_query("settings", $row);
	}
    rebuild_settings();
    
   require_once MYBB_ROOT."/inc/plugins/myvids/myvids_templates.php";
    
    create_myvids_templates();
}

function myvids_deactivate() {
    global $db, $cache;
    
    $query = $db->simple_select("settinggroups", "gid", "name='myvids'");
    $gid = $db->fetch_field($query, 'gid');
    $db->delete_query("settinggroups", "gid='".$gid."'");
    $db->delete_query("settings", "gid='".$gid."'");    
    $db->delete_query("templates", "title LIKE 'myvids%'");
    rebuild_settings();
}

function myvids_uninstall() {
    global $db, $cache;
    
    $db->write_query("DROP TABLE ".TABLE_PREFIX."videos");
    $db->write_query("DROP TABLE ".TABLE_PREFIX."videocomments");
    $db->write_query("DROP TABLE ".TABLE_PREFIX."videocategories");
    $db->write_query("ALTER TABLE `".TABLE_PREFIX."usergroups` DROP `canusemyvids` , DROP `canaddvids` , DROP `cancommentvid` ;");
    $cache->update_usergroups();
}

function myvids_edit_group()
{
	global $run_module, $form_container, $lang, $form, $mybb;

	// If the current tab is "Users & Permissions", add plugin options
	if($run_module == 'user' && !empty($form_container->_title) & !empty($lang->users_permissions) & $form_container->_title == $lang->users_permissions)
	{

		// And generate the checkbox
		$myvids_options = array();
		$myvids_options[] = $form->generate_check_box('canusemyvids', 1, "Can access the MyVids mod?", array('checked' => $mybb->input['canusemyvids']));
		$myvids_options[] = $form->generate_check_box('canaddvids', 1, "Can add videos to the MyVids mod?", array('checked' => $mybb->input['canaddvids']));
		$myvids_options[] = $form->generate_check_box('cancommentvid', 1, "Can comment on videos?", array('checked' => $mybb->input['cancommentvid']));
		$myvids_options[] = $form->generate_check_box('needvidmod', 1, "Do videos need moderating before they display?", array('checked' => $mybb->input['needvidmod']));
		$myvids_options[] = $form->generate_check_box('canmodvid', 1, "Can this group moderate videos and comments?", array('checked' => $mybb->input['canmodvid']));

		
		$form_container->output_row("MyVids Permissions", '', '<div class="group_settings_bit">'.implode('</div><div class="group_settings_bit">', $myvids_options).'</div>');
	}
}

// Update group-bassed permission
function myvids_edit_group_do()
{
	global $updated_group, $mybb;

	$updated_group['canusemyvids'] = $mybb->input['canusemyvids'];
	$updated_group['canaddvids'] = $mybb->input['canaddvids'];
	$updated_group['cancommentvid'] = $mybb->input['cancommentvid'];
	$updated_group['needvidmod'] = $mybb->input['needvidmod'];
	$updated_group['canmodvid'] = $mybb->input['canmodvid'];
}

function myvids_prof() 
{
	global $db, $memprofile, $lang;
	
	$lang->load('myvids');

	$memprofile['numvids'] = $db->fetch_field($db->simple_select("videos", "COUNT(vid) as count", "uid =".intval($memprofile['uid'])), "count");
	$memprofile['numcomments'] = $db->fetch_field($db->simple_select("videocomments", "COUNT(vcid) as count", "uid =".intval($memprofile['uid'])), "count");
		
}
?>