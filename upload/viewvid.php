<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'viewvid.php');

$templatelist = "myvids_commentpagination,myvids_addcomment,myvids_watch,myvids_editbar,myvids_singlecomment";

require_once "./global.php";

global $mybb, $cache, $db, $lang, $theme, $addcomment, $usergroups_cache, $plugins;

$plugins->run_hooks("myvids_start");

$usergroups_cache = $cache->read("usergroups");	

if (!$usergroups_cache[$mybb->user['usergroup']]['canusemyvids']) {
    error_no_permission();
}

$lang->load("myvids");

$vid = intval($mybb->input['vid']);

if ($mybb->settings['myvidseo'] && !$vid) {
    $vidseolink = $db->escape_string($mybb->input['vidseolink']);
}

add_breadcrumb($lang->myvids_base, "myvids.php");
add_breadcrumb($lang->myvids_watch, "viewvid.php?vid=".$vid);

$plugins->run_hooks("myvids_watch_start");

if ($mybb->settings['myvidseo'] && $vidseolink != "") {
    $query = $db->query("SELECT `".TABLE_PREFIX."videos`.*, `".TABLE_PREFIX."users`.* FROM `".TABLE_PREFIX."videos` INNER JOIN `".TABLE_PREFIX."users` ON `".TABLE_PREFIX."videos`.`uid` = `".TABLE_PREFIX."users`.`uid` WHERE  `".TABLE_PREFIX."videos`.`vidseolink` = '".$vidseolink."' LIMIT 1");
}
else {
   $query = $db->query("SELECT `".TABLE_PREFIX."videos`.*, `".TABLE_PREFIX."users`.* FROM `".TABLE_PREFIX."videos` INNER JOIN `".TABLE_PREFIX."users` ON `".TABLE_PREFIX."videos`.`uid` = `".TABLE_PREFIX."users`.`uid` WHERE  `".TABLE_PREFIX."videos`.`vid` = ".$vid." LIMIT 1"); 
}

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
	"me_username" => $mybb->user['username'],
);

while($result = $db->fetch_array($query))
{
    $vidtitle = $result['vidname'];
    $user = str_replace("{username}", $result['username'], $usergroups_cache[$result['usergroup']]['namestyle']);
    $userid = $result['uid'];
    $viddesc =  $parser->parse_message($result['viddesc'], $options);;
    $viddate = explode(" ", $result['viddate']);
    $videodate = $viddate[0];
    $videotime = substr($viddate[1], 0, 5);
    $video = getvideoembed($result['vidurl']);
    $vid = intval($result['vid']);
    $catid = intval($result['vidcat']);
    
    $catname = $db->simple_select("videocategories", "catname", "catid = ".$catid);
    
    $catname = $db->fetch_field($catname, "catname");
    
    $headerinclude .= "<meta name=\"description\" content=\"".str_replace("\\n", " ", substr(strip_tags($viddesc), 0, 240))."\" />";
    
	$query = $db->simple_select("videocomments", "COUNT(vcid)", "vid=".$vid);
	$commentpages = $db->fetch_field($query, "COUNT(vcid)");
	$pages = ceil($commentpages / 10);
	
	if (isset($mybb->input['commentpage']) && $mybb->input['commentpage'] != "" && $mybb->input['commentpage'] != 0) {
		$commentpage = intval($mybb->input['commentpage']);
		global $commentpage;
	}
	
	global $vid;
	include "./videocomments.php";
	
	
	if ($pages != 1 && $pages != 0) {
		for($i=1; $i<=$pages; $i++) {
		
			if ($i == $commentpage) {
				$class = " pagination_current";
			}
			elseif ($i == 1 && $commentpage == "") {
				$class = " pagination_current";
			}
			else {
				$class = "";
			}
			
			$paging .= '<a class="pagination_page'.$class.'" id="'.$i.'" href="?page='.$i.'">'.$i.'</a> ';
		}
		
		eval("\$pagination = \"".$templates->get('myvids_commentpagination')."\";");
	}


    if ($mybb->usergroup['cancommentvid']) {
        eval("\$addcomment = \"".$templates->get('myvids_addcomment')."\";");
    }
}

$plugins->run_hooks("myvids_watch_end");

eval("\$page = \"".$templates->get('myvids_watch')."\";");
output_page($page);

function getvideoembed($video) {

	global $mybb, $plugins, $db;
    
    $parsed_url = parse_url($video);

    if ($parsed_url['host'] == "youtube.com" || $parsed_url['host'] == "www.youtube.com") {
        parse_str($parsed_url['query'], $parsed_query);
        $video = '<iframe title="YouTube video player" width="640" height="390" src="http://www.youtube.com/embed/'.$parsed_query['v'].'?rel=0" frameborder="0" allowfullscreen></iframe>';
    }
    elseif ($parsed_url['host'] == "vimeo.com" || $parsed_url['host'] == "www.vimeo.com") {
        $video = '<iframe src="http://player.vimeo.com/video'.$parsed_url['path'].'" width="640" height="390" frameborder="0"></iframe>';
    }
    elseif ($parsed_url['host'] == "dailymotion.com" || $parsed_url['host'] == "www.dailymotion.com") {
		$video = explode("_" , $parsed_url['path']);
        $video = '<iframe frameborder="0" width="640" height="390" src="http://www.dailymotion.com/embed/video/'.substr( $video[0], 7).'?theme=none"></iframe>';
    }
	elseif ("http://".$parsed_url['host'] == $mybb->settings['bburl']) {
		$video = '<script type="text/javascript" src="myvids/swfobject.js"></script>
 
<div id="mediaspace">This text will be replaced</div>
 
<script type="text/javascript">
  var so = new SWFObject("myvids/player.swf","mpl","640","360","9");
  so.addParam("allowfullscreen","true");
  so.addParam("allowscriptaccess","always");
  so.addParam("wmode","opaque");
  so.addVariable("file","'.$video.'");
  so.write("mediaspace");
</script>';
	}
	$plugins->run_hooks("myvids_video_parse_end");

    return $video;
}

?>
