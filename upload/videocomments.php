<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'videocomments.php');

require_once "./global.php";

global $mybb, $cache, $db, $lang, $theme, $addcomment, $vid;

$lang->load("myvids");

if ($mybb->input['vid']) {
	$vid = intval($mybb->input['vid']);
}

if (!$mybb->input['page'] || $mybb->input['page'] == 1) {
	$page = 0;
}
else {
	$page = (intval($mybb->input['page']) - 1) * 10;
}

if (isset($commentpage) && $commentpage != "" && $commentpage != 0) {
	$page = (intval($commentpage) - 1) * 10;
}

$usergroups_cache = $cache->read("usergroups");	

require_once MYBB_ROOT.'inc/class_parser.php';
$parser = new postParser;

$options = array(
	"allow_html" => 0,
	"allow_mycode" => 1,
	"allow_smilies" => 1,
	"allow_imgcode" => 0,
	"filter_badwords" => 1,
	"nl2br" => 1,
	"allow_videocode" => 0,
	"me_username" => $comment['username'],
);

$vidcomments = $db->query("SELECT `".TABLE_PREFIX."videocomments`.*, `".TABLE_PREFIX."users`.* FROM `".TABLE_PREFIX."videocomments` INNER JOIN `".TABLE_PREFIX."users` ON `".TABLE_PREFIX."videocomments`.`uid` = `".TABLE_PREFIX."users`.`uid` WHERE `".TABLE_PREFIX."videocomments`.`vid` = ".$vid." ORDER BY ABS(`".TABLE_PREFIX."videocomments`.`commentdate`) DESC LIMIT ".$page.", 10");

if ($db->num_rows($vidcomments) == 0) {
	eval("\$videocomments = \"".$templates->get('myvids_nocomments')."\";");
}
else {

    while ($comment = $db->fetch_array($vidcomments)) {
        $bgcolor = alt_trow();
        
        if ($comment['avatar'] != "") {
            $comment['avatar'] = "<img src=\"".$mybb->settings['bburl']."/".$comment['avatar']."\" alt=\"".$comment['username']."s avatar\" width=\"64\" height=\"64\" /><br /><br />";
        }
		
        $comment['formattedname'] = str_replace("{username}", $comment['username'], $usergroups_cache[$comment['usergroup']]['namestyle']);
		
		$comment['vidcomment_noparse'] = $comment['vidcomment'];

		$comment['vidcomment'] = $parser->parse_message($comment['vidcomment'], $options);
		
		
		$commentdate = explode(" ", $comment['commentdate']);
		$dateofcomment = explode("-", $commentdate[0]);
		$timeofcomment = explode(":", $commentdate[1]);
		
		$formatteddatetime = mktime($timeofcomment[0], $timeofcomment[1], $timeofcomment[2], $dateofcomment[1], $dateofcomment[2], $dateofcomment[0]);
		$comment['commenttime'] = gmdate("D, d \of F Y  -  g:i A", $formatteddatetime);
		
		unset($commentdate, $dateofcomment, $timeofcomment);
	
		if ( $mybb->user['uid'] == $comment['uid'] || $usergroups_cache[$mybb->user['usergroup']]['canmodvid'] != 0) {
			eval("\$editbar = \"".$templates->get('myvids_editbar')."\";");
			
			$editform = '					<div class="editform'.$comment['vcid'].'" style="display: none;">
						<form action="myvids_modify.php?type=c&amp;vcid='.$comment['vcid'].'" method="post" class="editform'.$comment['vcid'].'form">
							<textarea name="commentval" id="newcommentval'.$comment['vcid'].'" style="width: 50%; height: 100px;">'.$comment['vidcomment_noparse'].'</textarea><br>
							<input type="submit" value="'.$lang->myvids_edit_comment.'"> <input type="button" name="canceledit'.$comment['vcid'].'" value="'.$lang->myvids_cancel.'"> 
						</form>
					</div>';
		}
		else {
			$editbar = "";
		}
	
        eval("\$videocomments .= \"".$templates->get('myvids_singlecomment')."\";");
    }
}
	
if ($_SERVER['SCRIPT_FILENAME'] == str_replace("\\", "/", MYBB_ROOT."videocomments.php")) {
	print $videocomments;
}
?>