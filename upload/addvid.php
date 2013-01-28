<?php
	define("IN_MYBB", 1);
	define('THIS_SCRIPT', 'addvid.php');
	
	$templatelist = "myvids_addvid";

	require_once "./global.php";

	global $mybb, $cache, $db, $lang, $theme, $plugins;

	$usergroups_cache = $cache->read("usergroups");	

	if (!$usergroups_cache[$mybb->user['usergroup']]['canusemyvids'] || !$usergroups_cache[$mybb->user['usergroup']]['canaddvids']) {
	    error_no_permission();
	}

	$lang->load("myvids");

	add_breadcrumb($lang->myvids_base, "myvids.php");
	add_breadcrumb($lang->myvids_addvid, "addvid.php");

	$action = $mybb->input['action'];
	
	$plugins->run_hooks("myvids_addvid_start");

	if ($action == "add") {
	    $uid = intval($mybb->user['uid']);
	    $vidtitle = trim($db->escape_string(strip_tags($mybb->input['vidname'])));
		
		if ($_FILES && $db->escape_string($mybb->input['vidtype']) == "upload") {
			require_once MYBB_ROOT."inc/functions_upload.php";
			$upload = upload_file($_FILES['vidfile'], $mybb->settings['myviduploaddir']);
			
			if ($upload['error'] == 1) {
				error("Video upload failed");
			}
			else {
				$vidurl = $mybb->settings['bburl'].substr($upload['path'], 1)."/".$upload['filename'];
			}
		}
		else {
			$vidurl = trim($db->escape_string($mybb->input['vidurl']));
			
			$parsed_url = parse_url($vidurl);
			
			$supported_sites = array("youtube.com", "www.youtube.com", "vimeo.com", "www.vimeo.com", "dailymotion.com", "www.dailymotion.com");
			if (!in_array($parsed_url['host'], $supported_sites)) {
				error($lang->myvids_unsupported_embed);
			}
			
		}
		
	    $viddesc = $db->escape_string(strip_tags($mybb->input['viddesc']));
	    $vidcat = $db->escape_string($mybb->input['vidcat']);
		
		if ( empty($vidtitle) || (empty($vidurl) && !$_FILES) ) {
			die("You must enter a title and URL");
		}
	    
	    $vidcat = $db->fetch_field($db->simple_select("videocategories", "catid", "catname = '".$vidcat."'"),'catid');
	    
	    if (!$vidcat) {
		$vidcat = 1;
	    }
	    
	    $vidseolink = substr(strtolower(str_replace(" ", "-", preg_replace('/[^a-zA-Z0-9_ ]/s', '', $vidtitle))), 0, 30);

	    
	    if ($db->num_rows($db->simple_select("videos", "vidseolink", "vidseolink = '".$vidseolink."'")) >= 1) {
			$query = $db->simple_select("videos", "vid", "", array("order_by" => 'vid', "order_dir" => 'DESC', "limit" => 1));
			$vid = $db->fetch_field($query, "vid") + 1;
			$vidseolink = $vid."-".$vidseolink;
	    }
	    
	    if (substr($vidseolink, -1) == "-") {
		$vidseolink = substr($vidseolink, 0, -1);
	    }
		
		$vidmod = 0;
		
		if ($usergroups_cache[$mybb->user['usergroup']]['needvidmod'] != 0) {
			$vidmod = 1;
		}
	    
	    if ($db->query("INSERT INTO ".TABLE_PREFIX."videos(uid, vidname, vidurl, viddesc, viddate, vidcat, vidseolink, vidmod) VALUES (".$uid.", '".$vidtitle."', '".$vidurl."', '".$viddesc."', NOW(), '".$vidcat."', '".$vidseolink."', ".$vidmod.");")) {
			$redirect_url = htmlentities("viewvid.php?vid=".$db->insert_id());
			redirect($redirect_url, $lang->myvids_videoadded);	
	    }
	    else {
			$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);
			redirect($redirect_url, $lang->myvids_unexpectederror);
	    }
	    
	}
	else {
	    $query = $db->simple_select("videocategories", "catname");
	    
	    $categories = "<select name=\"vidcat\" style=\"width: 95%;\">";
	    while ($result = $db->fetch_array($query)) {
			$categories .= "<option>".$result['catname']."</option>";
	    }
	    $categories .= "</select>";
		
		if ($mybb->settings['myviduploading'] != 0) {
			$vidupload = '                <tr>
                    <td class="trow1 smalltext" width="20%"><label for="vidname">'.$lang->myvids_vidtype.'</label></td>
                    <td class="trow1">
                        <input type="radio" name="vidtype" value="embed" checked> '.$lang->myvids_embed.' &nbsp; 
						<input type="radio" name="vidtype" value="upload"> '.$lang->myvids_upload.'<br>
                    </td>
                </tr>
                <tr id="embed">
                    <td class="trow2 smalltext"><label for="vidurl">'.$lang->myvids_vidurl.'</label></td>
                    <td class="trow2">
                        <input class="textbox" type="text" name="vidurl" style="width: 95%" />
                    </td>
                </tr>
                <tr id="upload" style="display: none;">
                    <td class="trow2 smalltext"><label for="vidfile">'.$lang->myvids_choosefile.'</label></td>
                    <td class="trow2">
						<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
						<input name="vidfile" type="file" style="width: 95%" />
                    </td>
                </tr>';
		}
		else {
			$vidupload = '                <tr id="embed">
                    <td class="trow2 smalltext"><label for="vidurl">'.$lang->myvids_vidurl.'</label></td>
                    <td class="trow2">
                        <input class="textbox" type="text" name="vidurl" style="width: 95%" />
                    </td>
                </tr>';
		}
	    
	    eval("\$page = \"".$templates->get('myvids_addvid')."\";");
	    output_page($page);
	}
	
	$plugins->run_hooks("myvids_addvid_end");
?>
