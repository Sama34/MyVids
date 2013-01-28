<?php
global $mybb, $db, $page, $lang, $table, $plugins;

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if ($db->table_exists("videocomments") && $db->table_exists("videocomments") && $mybb->settings['myvidson']) {
	//all is well
}
else {
	die("Please install and activate MyVids before trying to access this area");
}

$lang->load("myvids");

$page->add_breadcrumb_item($lang->myvids_base, "index.php?module=myvids");
$page->add_breadcrumb_item($lang->myvids_manage_categories, "index.php?module=myvids-manage-categories");

$plugins->run_hooks("myvids_manage_categories_start");

$page->output_header($lang->myvids_manage_categories);

$sub_tabs['manage-categories'] = array(
	'title' => $lang->myvids_manage_categories,
	'link' => "index.php?module=myvids-manage-categories",
	'description' => $lang->myvids_manage_categories_desc,
);

$sub_tabs['add-category'] = array(
	'title' => $lang->myvids_add_category,
	'link' => "index.php?module=myvids-manage-categories&amp;action=add",
	'description' => $lang->myvids_add_categories_desc,
);

$action = $mybb->input['action'];
$catid = intval($mybb->input['catid']);

if ($action == "delete") {
	if ($db->delete_query("videocategories", "catid = ".$catid)) {
		flash_message($lang->myvids_message_success, "success");
		log_admin_action($vcid, $lang->myvids_message_success);
		admin_redirect("index.php?module=myvids-manage-categories");
	}
	else {
		flash_message($lang->myvids_message_error, "error");
		admin_redirect("index.php?module=myvids-manage-categories");	
	}	
}
elseif ($action == "modify") {
	$query = $db->simple_select("videocategories", "*", "catid = '".$catid."'");

	$form = new Form("index.php?module=myvids-manage-categories&amp;action=save-edit&amp;catids=".$catid, "post", "edit");
	$form_container = new FormContainer($lang->myvids_edit_video);

	while($row = $db->fetch_array($query))
	{
		$form_container->output_row("Category Name", "The name of the category", $form->generate_text_box('catname', $row['catname'], array('id' => 'catname')), 'catname');
	}
	$form_container->end();
	$buttons[] = $form->generate_submit_button($lang->myvids_edit_video, array('name' => 'edit'));
	$form->output_submit_wrapper($buttons);
	$form->end();
}
elseif ($action == "save-edit") {
	$catname = $db->escape_string($mybb->input['catname']);
	
	if ($db->update_query("videocategories", array("catname" => $catname), "catid =".$catid)) {
		flash_message($lang->myvids_message_success, "success");
		log_admin_action($vcid, $lang->myvids_message_success);
		admin_redirect("index.php?module=myvids-manage-categories");		
	}
	else {
		flash_message($lang->myvids_message_error, "error");
		admin_redirect("index.php?module=myvids-manage-categories");
	}
}
elseif ($action == "add") {
	$page->output_nav_tabs($sub_tabs, 'add-category');

	$form = new Form("index.php?module=myvids-manage-categories&amp;action=save-add", "post", "edit");
	$form_container = new FormContainer($lang->myvids_add_category);
	$form_container->output_row("Category Name", "The name of the category", $form->generate_text_box('catname', "", array('id' => 'catname')), 'catname');
	$form_container->end();
	$buttons[] = $form->generate_submit_button($lang->myvids_add_category, array('name' => 'edit'));
	$form->output_submit_wrapper($buttons);
	$form->end();	
}
elseif ($action == "save-add") {
	$catname = $db->escape_string($mybb->input['catname']);
	
	if ($db->insert_query("videocategories", array("catname" => $catname))) {
		flash_message($lang->myvids_message_success, "success");
		log_admin_action($vcid, $lang->myvids_message_success);
		admin_redirect("index.php?module=myvids-manage-categories");		
	}
	else {
		flash_message($lang->myvids_message_error, "error");
		admin_redirect("index.php?module=myvids-manage-categories");
	}	
}
else {
	$page->output_nav_tabs($sub_tabs, 'manage-categories');
	$table = new Table;
	
	$table->construct_header($lang->myvids_catname);
	$table->construct_header($lang->myvids_actions);
	
	$query = $db->simple_select("videocategories", "*");
	if ($db->num_rows($query) == 0) {
		$table->construct_cell($lang->myvids_nocategories, array('colspan' => '2'));
		$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));
	}
	else {
		while ($row = $db->fetch_array($query)) {
			$table->construct_cell('<strong>'.$row['catname'].'</strong>');
			$popup = new PopupMenu("category_{$row['catid']}", $lang->options);
			$popup->add_item($lang->myvids_edit_video, "index.php?module=myvids-manage-categories&amp;action=modify&amp;catid={$row['catid']}");
			$popup->add_item($lang->myvids_delete_video, "index.php?module=myvids-manage-categories&amp;action=delete&amp;catid={$row['catid']}");
			$table->construct_cell($popup->fetch(), array("class" => "align_center"));
			$table->construct_row(array('class' => 'alt_row', 'no_alt_row' => 1));
		}
	}
	
	$table->output($lang->myvids_manage_categories);
}

$page->output_footer();
?>
