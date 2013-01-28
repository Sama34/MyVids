<?php
global $mybb, $db;

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if ($db->table_exists("videos") && $db->table_exists("videocomments") && $mybb->settings['myvidson']) {
	//all is well
}
else {
	die("Please install and activate MyVids before trying to access this area");
}

$query = $db->simple_select("settinggroups", "gid", "name = 'myvids'");
$gid = $db->fetch_field($query, "gid");

admin_redirect("index.php?module=config-settings&action=change&gid=".$gid);
?>