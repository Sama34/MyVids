<?php

/***************************************************************************
	MyVids plugin ()
	Copyright © 2011-2012 Euan T

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
****************************************************************************/

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