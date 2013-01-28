<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function myvids_meta()
{
	global $page, $lang, $mybb, $db, $plugins;
        
        $lang->load("myvids", false, true);
	
	$sub_menu = array();
	$sub_menu['10'] = array("id" => "myvids_home", "title" => $lang->myvids_home, "link" => "index.php?module=myvids");
	$sub_menu['20'] = array("id" => "myvids_settings", "title" => $lang->myvids_settings, "link" => "index.php?module=myvids-settings");
	$sub_menu['30'] = array("id" => "myvids_usergroup_settings", "title" => $lang->myvids_usergroup_settings, "link" => "index.php?module=myvids-usergroup-settings");	$sub_menu['50'] = array("id" => "myvids_manage_comments", "title" => $lang->myvids_manage_comments, "link" => "index.php?module=myvids-manage-comments");
	$sub_menu['40'] = array("id" => "myvids_manage_categories", "title" => $lang->myvids_manage_categories, "link" => "index.php?module=myvids-manage-categories");
	$sub_menu['50'] = array("id" => "myvids_manage_videos", "title" => $lang->myvids_manage_videos, "link" => "index.php?module=myvids-manage-videos");
	
	$plugins->run_hooks_by_ref("admin_myvids_menu", $sub_menu);
	
	$page->add_menu_item($lang->myvids_title, "myvids", "index.php?module=myvids", 60, $sub_menu);
	
	return true;
}

function myvids_action_handler($action)
{
	global $page, $lang, $plugins;
	
	$page->active_module = "myvids";

	$actions = array(
		'home' => array('active' => 'home', 'file' => 'home.php'),
		'settings' => array('active' => 'settings', 'file' => 'settings.php'),
		'usergroup-settings' => array('active' => 'usergroup-settings', 'file' => 'usergroup-settings.php'),
		'manage-categories' => array('active' => 'myvids_manage_categories', 'file' => 'manage-categories.php'),		
		'manage-videos' => array('active' => 'myvids_manage_videos', 'file' => 'manage-videos.php'),
	);
	
	$plugins->run_hooks_by_ref("myvids_action_handler", $actions);

	if(isset($actions[$action]))
	{
		$page->active_action = $actions[$action]['active'];
		return $actions[$action]['file'];
	}
	else
	{
		$page->active_action = "myvids_home";
		return "home.php";
	}
}
?>