<?php

/***************************************************************************
	MyVids plugin ()
	Copyright © 2011-2012 Euan T

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
****************************************************************************/

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
