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
$page->add_breadcrumb_item($lang->myvids_manage_videos, "index.php?module=myvids-manage-videos");

$plugins->run_hooks("myvids_manage_vids_start");

$page->output_header($lang->myvids_manage_videos);

$sub_tabs['manage'] = array(
	'title' => $lang->myvids_manage_videos,
	'link' => "index.php?module=myvids-manage-videos",
	'description' => $lang->myvids_manage_videos_desc
);

$sub_tabs['mod'] = array(
	'title' => $lang->myvids_mod_videos,
	'link' => "index.php?module=myvids-manage-videos&myvidsarea=mod",
	'description' => $lang->myvids_mod_videos_desc
);



$myvidsarea = $mybb->input['myvidsarea'];
$action = $mybb->input['action'];
$vid = intval($mybb->input['vid']);

if ($myvidsarea == "mod") {
	$page->output_nav_tabs($sub_tabs, 'mod');
	
	if ($action == "approve") {
		if ($db->update_query("videos", "vidmod = 0", "vid =".$vid)) {
			flash_message($lang->myvids_success_message, "success");
			admin_redirect("index.php?module=myvids-manage-videos");
		}
		else {
			flash_message($lang->myvids_error_message, "error");
			admin_redirect("index.php?module=myvids-manage-videos");
		}
	}
	else {
		$table = new Table;
		
		$table->construct_header($lang->myvids_videotitle);
		$table->construct_header($lang->myvids_addedby);
		$table->construct_header($lang->myvids_desc);
		$table->construct_header($lang->myvids_actions);
		
		$query = $db->query("SELECT `".TABLE_PREFIX."videos`.*, username FROM `".TABLE_PREFIX."videos` INNER JOIN `".TABLE_PREFIX."users` ON `".TABLE_PREFIX."videos`.`uid` = `".TABLE_PREFIX."users`.`uid` WHERE vidmod = 1 ORDER BY vid LIMIT 25");
		
		if ($db->num_rows($query) == 0) {
			$table->construct_cell($lang->myvids_novideos, array('colspan' => '4'));
			$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));
		}
		else {
			while ($row = $db->fetch_array($query)) {
				$table->construct_cell('<strong><a href="index.php?module=myvids-manage-videos&amp;action=modify&amp;vid='.$row['vid'].'">'.$row['vidname'].'</a></strong>');
				$table->construct_cell($row['username']);
				$table->construct_cell(substr($row['viddesc'], 0, 50)."[...]");
				$popup = new PopupMenu("video_{$row['vid']}", $lang->options);
				$popup->add_item($lang->myvids_approve_video, "index.php?module=myvids-manage-videos&amp;myvidsarea=mod&amp;action=approve&amp;vid={$row['vid']}");
				$popup->add_item($lang->myvids_delete_video, "index.php?module=myvids-manage-videos&amp;action=delete&amp;vid={$row['vid']}");
				$table->construct_cell($popup->fetch(), array("class" => "align_center"));
				$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));
			}
		}
		$table->output($lang->myvids_mod_videos);
	}
}
else {

	$page->output_nav_tabs($sub_tabs, 'manage');
	
	if ($action == "delete") {
		if ($db->delete_query("videos", "vid = ".$vid) && $db->delete_query("videocomments", "vid = ".$vid)) {
			flash_message($lang->myvids_success_message, "success");
			log_admin_action($vid, "Deleted");
			admin_redirect("index.php?module=myvids-manage-videos");
		}
		else {
			flash_message($lang->myvids_error_message, "error");
			admin_redirect("index.php?module=myvids-manage-videos");	
		}	
	}
	elseif ($action == "modify") {
		$query = $db->simple_select("videos", "*", "vid = '".$vid."'", array("order_by" => 'ABS(vid)', "order_dir" => 'DESC'));

		$form = new Form("index.php?module=myvids-manage-videos&amp;action=save-edit&amp;vid=".$vid, "post", "edit");
		$form_container = new FormContainer($lang->myvids_edit_video);

		while($row = $db->fetch_array($query))
		{
			$cats = $db->simple_select("videocategories", "*");
			
			while ($cat = $db->fetch_array($cats)) {
				$categories[$cat['catid']] = $cat['catname'];
			}
						
			$vidcat = $db->fetch_field($db->simple_select("videocategories", "catname", "catid = ".$row['vidcat']), "catname");
			
			$form_container->output_row("Video Title", "The video's title", $form->generate_text_box('vidname', $row['vidname'], array('id' => 'vidname')), 'vidname');
			$form_container->output_row("Video URL", "The URL of the embedded video", $form->generate_text_box('vidurl', $row['vidurl'], array('id' => 'vidurl')), 'vidurl');
			$form_container->output_row("Video Description", "The description of the video", $form->generate_text_area('viddesc', $row['viddesc'], array('id' => 'viddesc')), 'viddesc');
			$form_container->output_row("Video Category", "", $form->generate_select_box('vidcat', $categories, $vidcat), 'vidcat');
		}
		$form_container->end();
		$buttons[] = $form->generate_submit_button($lang->myvids_edit_video, array('name' => 'edit'));
		$form->output_submit_wrapper($buttons);
		$form->end();
	}
	elseif ($action == "save-edit") {
		$title = $db->escape_string($mybb->input['vidname']);
		$url = $db->escape_string($mybb->input['vidurl']);
		$desc = $db->escape_string($mybb->input['viddesc']);
		$cat = intval($mybb->input['vidcat']);
				
		if ($title != $db->fetch_field($db->simple_select("videos", "vidname", "vid = ".$vid), "vidname")) {
			$vidseolink = substr(strtolower(str_replace(" ", "-", preg_replace('/[^a-zA-Z0-9_ ]/s', '', $title))), 0, 30);

				
			if ($db->num_rows($db->simple_select("videos", "vidseolink", "vidseolink = '".$vidseolink."'")) >= 1) {
				$vidseoquery = $db->simple_select("videos", "vid", "", array("order_by" => 'vid', "order_dir" => 'DESC', "limit" => 1));
				$vidseovid = $db->fetch_field($vidseoquery, "vid") + 1;
				$vidseolink = $vidseovid."-".$vidseolink;
			}
		}
		else {
			$vidseolink = $db->fetch_field($db->simple_select("videos", "vidseolink", "vid = ".$vid), "vidseolink");
		}
		
		if ($db->update_query("videos", array("vidname" => $title, "vidurl" => $url, "viddesc" => $desc, "vidseolink" => $vidseolink, "vidcat" => $cat), "vid =".$vid)) {
			flash_message($lang->myvids_success_message, "success");
			log_admin_action($vid, $lang->myvids_success_message);
			admin_redirect("index.php?module=myvids-manage-videos");		
		}
		else {
			flash_message($lang->myvids_error_message, "error");
			admin_redirect("index.php?module=myvids-manage-videos");
		}
	}
	else {
		$table = new Table;
		
		$table->construct_header($lang->myvids_videotitle);
		$table->construct_header($lang->myvids_addedby);
		$table->construct_header($lang->myvids_desc);
		$table->construct_header($lang->myvids_actions);
		
		$query = $db->query("SELECT `".TABLE_PREFIX."videos`.*, username FROM `".TABLE_PREFIX."videos` INNER JOIN `".TABLE_PREFIX."users` ON `".TABLE_PREFIX."videos`.`uid` = `".TABLE_PREFIX."users`.`uid` WHERE vidmod = 0 ORDER BY vid LIMIT 25");
		if ($db->num_rows($query) == 0) {
			$table->construct_cell($lang->myvids_novideos, array('colspan' => '4'));
			$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));
		}
		else {
			while ($row = $db->fetch_array($query)) {
				$table->construct_cell('<strong><a href="index.php?module=myvids-manage-videos&amp;action=modify&amp;vid='.$row['vid'].'">'.$row['vidname'].'</a></strong>');
				$table->construct_cell($row['username']);
				$table->construct_cell(substr($row['viddesc'], 0, 50)."[...]");
				$popup = new PopupMenu("video_{$row['vid']}", $lang->options);
				$popup->add_item($lang->myvids_edit_video, "index.php?module=myvids-manage-videos&amp;action=modify&amp;vid={$row['vid']}");
				$popup->add_item($lang->myvids_delete_video, "index.php?module=myvids-manage-videos&amp;action=delete&amp;vid={$row['vid']}");
				$table->construct_cell($popup->fetch(), array("class" => "align_center"));
				$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));
			}
		}
		
		$table->output($lang->myvids_manage_videos);
	}
}

$page->output_footer();
?>