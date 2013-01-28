<?php
global $mybb, $db, $page, $lang, $table, $plugins;

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

$lang->load("myvids");

$page->add_breadcrumb_item($lang->myvids_base, "index.php?module=myvids");
$page->add_breadcrumb_item($lang->myvids_home, "index.php?module=myvids-home");

$plugins->run_hooks("myvids_home_start");

$page->output_header($lang->myvids_home);

$sub_tabs['home'] = array(
	'title' => $lang->myvids_home,
	'link' => "index.php?module=myvids-home",
	'description' => $lang->myvids_home_description
);

$page->output_nav_tabs($sub_tabs, 'home');

$table = new Table;
$table->construct_cell("<strong>".$lang->myvids_home."</strong>".$lang->myvids_welcome_message, array('colspan' => 2));
$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));

$num_videos = $db->simple_select("videos", "COUNT(vid)");
$num_videos = $db->fetch_field($num_videos, "COUNT(vid)");

$table->construct_cell("<strong>".$lang->myvids_numvideos."</strong>");
$table->construct_cell($num_videos);
$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));

$num_comments = $db->simple_select("videocomments", "COUNT(vcid)");
$num_comments = $db->fetch_field($num_comments, "COUNT(vcid)");

$table->construct_cell("<strong>".$lang->myvids_numcomments."</strong>");
$table->construct_cell($num_comments);
$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));

$table->output($lang->myvids_home);

$page->output_footer();
?>