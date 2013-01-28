<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'myvids.php');

$templatelist = "myvids_index";

require_once "./global.php";

global $mybb, $cache, $db, $lang, $theme;

$plugins->run_hooks("myvids_start");

$usergroups_cache = $cache->read("usergroups");	

if (!$usergroups_cache[$mybb->user['usergroup']]['canusemyvids']) {
    error_no_permission();
}

$lang->load("myvids");

add_breadcrumb($lang->myvids_home, "myvids.php");
add_breadcrumb($lang->myvids_view_category, "myvids.php");

$vidcat = intval($mybb->input['cat']);

$latestvideos = $db->simple_select("videos", "*", "vidcat = ".$vidcat, array("order_by" => 'ABS(viddate)', "order_dir" => 'DESC', "limit" => 10));

while($result = $db->fetch_array($latestvideos))
{
    $bgcolor = alt_trow();
    
    if ($mybb->settings['myvidseo'] && $result['vidseolink'] != "") {
        $latestvideolist .= "<tr>\n
        <td class=\"".$bgcolor."\"><strong><a href=\"video-".$result['vidseolink'].".html\">".$result['vidname']."</a></strong></td>\n
        <td class=\"".$bgcolor."\">".substr($result['viddesc'], 0, 70)."[...]</td>\n
    </tr>\n";
    }
    else {
        $latestvideolist .= "<tr>\n
        <td class=\"".$bgcolor."\"><strong><a href=\"viewvid.php?vid=".$result['vid']."\">".$result['vidname']."</a></strong></td>\n
        <td class=\"".$bgcolor."\">".substr($result['viddesc'], 0, 70)."[...]</td>\n
    </tr>\n";
    }
}

eval("\$page = \"".$templates->get('myvids_index')."\";");
output_page($page);
?>
