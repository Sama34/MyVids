<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'searchmyvids.php');

$templatelist = "myvids_index,multipage_page_current,multipage_page,multipage_nextpage,multipage_prevpage,multipage";

require_once "./global.php";
require_once "./inc/functions_search.php";

global $mybb, $cache, $db, $lang, $theme;

$plugins->run_hooks("myvids_search_start");

$usergroups_cache = $cache->read("usergroups");	

if (!$usergroups_cache[$mybb->user['usergroup']]['canusemyvids']) {
    error_no_permission();
}

$lang->load("myvids");
$lang->load("search");

add_breadcrumb($lang->myvids_home, "myvids.php");
add_breadcrumb($lang->myvids_search, "searchmyvids.php");

$searchterm = clean_keywords($db->escape_string(trim($mybb->input['searchterm'])));

if (strlen($searchterm) < $mybb->settings['minsearchword']) {
	$lang->error_minsearchlength = $lang->sprintf($lang->error_minsearchlength, $mybb->settings['minsearchword']);
	error($lang->error_minsearchlength);
}

$numvid = $db->fetch_field($db->simple_select("videos", "COUNT(vid) as count", "vidmod = 0 AND ( vidname LIKE '%".$searchterm."%' OR viddesc LIKE '%".$searchterm."%')"), "count");

$page = intval($mybb->input['page']);

if($page < 1) {
	$page = 1;
}

$multipage = multipage($numvid, 10, $page, $_SERVER['PHP_SELF']);

$searchresults = $db->simple_select("videos", "*", "vidmod = 0 AND ( vidname LIKE '%".$searchterm."%' OR viddesc LIKE '%".$searchterm."%')", array("order_by" => 'ABS(viddate)', "order_dir" => 'DESC', "limit" => (($page-1)*10).", 10"));

if ($db->num_rows($searchresults) == 0) {
	error($lang->error_nosearchresults);
}
else {

	while($result = $db->fetch_array($searchresults))
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
}

eval("\$page = \"".$templates->get('myvids_index')."\";");
output_page($page);
?>