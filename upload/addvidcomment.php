<?php

/***************************************************************************
	MyVids plugin ()
	Copyright © 2011-2012 Euan T

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
****************************************************************************/

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'addvidcomment.php');

require_once "./global.php";

global $mybb, $cache, $db, $lang, $theme, $addcomment;

$lang->load("myvids");
$usergroups_cache = $cache->read("usergroups");	

$vid = intval($mybb->input['vid']);
$vidcomment = trim($db->escape_string($mybb->input['comment']));
$uid = $mybb->user['uid'];
$method = $mybb->input['method'];

if ($uid == 0 || !$uid) {
	$uid = 0;
}

$myquery = $db->simple_select("users", "username, avatar", "uid = ".$uid, array("limit" => 1));

$newcomment = $db->fetch_array($myquery);

$avatar = "<img src=\"".$mybb->settings['bburl']."/".$newcomment['avatar']."\" alt=\"\" width=\"64\" height=\"64\" />";

$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);

if (!empty($vidcomment) && intval(strlen($vidcomment)) >= intval($mybb->settings['myvidcommentminlength']) && $usergroups_cache[$mybb->user['usergroup']]['cancommentvid'] != 0) {

	if ($db->query("INSERT INTO ".TABLE_PREFIX."videocomments(vid, uid, vidcomment, commentdate) VALUES (".$vid.", ".$uid.", '".$vidcomment."', NOW());")) {

		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
		$options = array(
			"allow_html" => 0,
			"allow_mycode" => 1,
			"allow_smilies" => 1,
			"allow_imgcode" => 0,
			"filter_badwords" => 1,
			"allow_videocode" => 0,
			"me_username" => $newcomment['username'],
		);

		$videocomment = $parser->parse_message($vidcomment, $options);
		
		
		if ($method == "js") {
			print "<tr>
				<td class=\"trow1\" width=\"70\" align=\"center\">
					<a href=\"member.php?action=profile&amp;uid=".$uid."\">".$avatar."</a><br /><br />
					<strong><a href=\"member.php?action=profile&amp;uid=".$uid."\">".$newcomment['username']."</a></strong>
				</td>
				<td class=\"trow1\">
					<p>".stripslashes(str_replace("\\n", "<br />", $videocomment))."</p>
				</td>
			</tr>";
			}
		else {
			redirect($redirect_url, $lang->myvids_commentadded);
		}
	}
	else {
		if ($method == "js") {
			print "<tr><td colspan=\"2\" align=\"center\">".$lang->myvids_unexpectederror."</td></tr>";
		}
		else {
			redirect($redirect_url, $lang->myvids_unexpectederror);
		}
	}
}
elseif ($usergroups_cache[$mybb->user['usergroup']]['cancommentvid'] == 0) {
	if ($method == "js") {
		print "<tr><td colspan=\"2\" align=\"center\">".$lang->myvids_nopermissions."</td></tr>";
	}
	else {
		redirect($redirect_url, $lang->myvids_nopermissions);
	}
}
else {
	if ($method == "js") {
		print $lang->myvids_commenterror;
	}
	else {
		redirect($redirect_url, $lang->myvids_commenterror);
	}
}
?>
