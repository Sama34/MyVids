<?php
	define("IN_MYBB", 1);
	define('THIS_SCRIPT', 'myvids_modify.php');
	
	require_once "./global.php";

	global $mybb, $cache, $db, $lang, $theme, $plugins;

	$usergroups_cache = $cache->read("usergroups");	

	if (!$usergroups_cache[$mybb->user['usergroup']]['canusemyvids']) {
	    error_no_permission();
	}
	
	$lang->load("myvids");

	$type = $mybb->input['type'];
	$vcid = intval($mybb->input['vcid']);
	$action = $mybb->input['action'];
	$method = $mybb->input['method'];
	$next = $mybb->input['next'];
	
	$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);
	
	$plugins->run_hooks("myvids_modify_start");

	switch ($type) {
		case "c":
		
			if ($action == "delete") {
				$uid = intval($db->fetch_field($db->simple_select("videocomments", "uid", "vcid = ".$vcid), "uid"));
				
				if ($mybb->user['uid'] == $uid || $usergroups_cache[$mybb->user['usergroup']]['canmodvid'] != 0) {
					if ($db->delete_query("videocomments", "vcid = ".$vcid)) {
						if ($method == "js") {
							print "deleted";
						}
						else {
							redirect($redirect_url, $lang->myvids_commentdeleted);
						}
					}
					else {
						if ($method == "js") {
							print "error";
						}
						else {
							redirect($redirect_url, $lang->myvids_unexpectederror);
						}
					}
				}
				else {
					redirect($redirect_url, $lang->myvids_nopermissions);
				}
			
			}
			else {
				add_breadcrumb($lang->myvids_base, "myvids.php");
				add_breadcrumb($lang->myvids_edit_comment, "myvids_modify.php?type=c&vcid=".$vcid);
			
				if ($method == "js") {
				
					$newcomment = trim($db->escape_string($mybb->input['commentval']));
					
					if (empty($newcomment) || intval(strlen($newcomment)) < intval($mybb->settings['myvidcommentminlength']) || $usergroups_cache[$mybb->user['usergroup']]['cancommentvid'] != 1) {
						die($lang->myvids_commenterror);
					}
					
					$uid = intval($db->fetch_field($db->simple_select("videocomments", "uid", "vcid = ".$vcid), "uid"));
					
					if ($mybb->user['uid'] == $uid || $usergroups_cache[$mybb->user['usergroup']]['canmodvid'] != 0) {
						if ($db->write_query('UPDATE '.TABLE_PREFIX.'videocomments SET vidcomment = "'.$newcomment.'" WHERE vcid = '.$vcid)) {
							
							require_once MYBB_ROOT.'inc/class_parser.php';
							$parser = new postParser;
							
							$options = array(
								"allow_html" => 0,
								"allow_mycode" => 1,
								"allow_smilies" => 1,
								"allow_imgcode" => 0,
								"filter_badwords" => 1,
								"allow_videocode" => 0,
								"me_username" => $mybb->user['username'],
							);

							echo str_replace("\\n", "<br />", $parser->parse_message($newcomment, $options));
						}
						else {
							echo "false";
						}
					}
					else {
						echo "false";
					}
					
				}
				else {
					if ($next == "doedit") {
						$newcomment = trim($db->escape_string($mybb->input['commentval']));
					
						$uid = intval($db->fetch_field($db->simple_select("videocomments", "uid", "vcid = ".$vcid), "uid"));
						$redirect_url = $mybb->input['redirect_url'];
						
						if (empty($vidcomment) && intval(strlen($vidcomment)) <= intval($mybb->settings['myvidcommentminlength']) && $usergroups_cache[$mybb->user['usergroup']]['cancommentvid'] != 1) {
							redirect($redirect_url, $lang->myvids_commenterror);
						}
						
						if ($mybb->user['uid'] == $uid || $usergroups_cache[$mybb->user['usergroup']]['canmodvid'] != 0) {
							if ($db->write_query('UPDATE '.TABLE_PREFIX.'videocomments SET vidcomment = "'.$db->escape_string($newcomment).'" WHERE vcid = '.$vcid)) {
								
								redirect($redirect_url, $lang->myvids_commentedited);
							}
							else {
								redirect($redirect_url, $lang->myvids_nopermissions);
							}
						}
						else {
							redirect($redirect_url, $lang->myvids_nopermissions);
						}
					
					}
					else {
						$currentcomment = $db->fetch_array($db->simple_select("videocomments", "*", "vcid = ".$vcid));
						
						if ($mybb->user['uid'] == $currentcomment['uid'] || $usergroups_cache[$mybb->user['usergroup']]['canmodvid'] != 0) {
							eval("\$page = \"".$templates->get('myvids_fulledit')."\";");
							output_page($page);
						}
						else {
							redirect($redirect_url, $lang->myvids_nopermissions);
						}
					}
				}
				
			}
			
			break;
			
		default:
			echo "false";
	}
	
	$plugins->run_hooks("myvids_modify_end");
?>