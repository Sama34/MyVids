<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'myvids.php');

$templatelist = "myvids_index,myvids_add_button,multipage_page_current,multipage_page,multipage_nextpage,multipage_prevpage,multipage";

require_once "./global.php";

global $mybb, $cache, $db, $lang, $theme;

$plugins->run_hooks("myvids_start");

$usergroups_cache = $cache->read("usergroups");	

if (!$usergroups_cache[$mybb->user['usergroup']]['canusemyvids']) {
    error_no_permission();
}

$lang->load("myvids");

add_breadcrumb($lang->myvids_home, "myvids.php");


$numvid = $db->fetch_field($db->simple_select("videos", "COUNT(vid) as count"), "count");

$page = intval($mybb->input['page']);

if($page < 1) {
	$page = 1;
}

$multipage = multipage($numvid, 10, $page, $_SERVER['PHP_SELF']);

$latestvideos = $db->simple_select("videos", "*", "vidmod = 0", array("order_by" => 'ABS(viddate)', "order_dir" => 'DESC', "limit" => (($page-1)*10).", 10"));


while($result = $db->fetch_array($latestvideos))
{
    $bgcolor = alt_trow();
    
    if ($mybb->settings['myvidseo'] && $result['vidseolink'] != "") {
	
		if (strlen($result['viddesc']) > 70) {
			$shortened = "[...]";
		}
		else {
			$shortened = "";
		}
		
        $latestvideolist .= "<tr>\n
        <td class=\"".$bgcolor."\"><strong><a href=\"video-".$result['vidseolink'].".html\">".$result['vidname']."</a></strong></td>\n
        <td class=\"".$bgcolor."\">".substr($result['viddesc'], 0, 70).$shortened."</td>\n
    </tr>\n";
    }
    else {
        $latestvideolist .= "<tr>\n
        <td class=\"".$bgcolor."\"><strong><a href=\"viewvid.php?vid=".$result['vid']."\">".$result['vidname']."</a></strong></td>\n
        <td class=\"".$bgcolor."\">".substr($result['viddesc'], 0, 70)."[...]</td>\n
    </tr>\n";
    }
}

if ($usergroups_cache[$mybb->user['usergroup']]['canaddvids']) {
    eval("\$addbutton = \"".$templates->get('myvids_add_button')."\";");
}

eval("\$page = \"".$templates->get('myvids_index')."\";");
output_page($page);
?>
