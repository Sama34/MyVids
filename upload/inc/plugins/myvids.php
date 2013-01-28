<?php

/***************************************************************************
	MyVids plugin ()
	Copyright © 2011-2012 Euan T

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
****************************************************************************/

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/* Hooks */

//User permissions
$plugins->add_hook('member_profile_end', 'myvids_profofile');
$plugins->add_hook('search_start', "myvids_search");

global $mybb, $templatelist;
if(THIS_SCRIPT == 'search.php' && $mybb->input['action'] == 'videos')
{
	$templatelist .= ', myvids_index';
}

// SEF Support
$mybb->settings['seo'] = (int)$mybb->settings['seo'];
if($mybb->settings['seo'] === 2)
{
	// Google SEO style urls
	define('MYDIVS_VIDEO_LINK', 'video-{1}');
	define('MYDIVS_CATEGORY_LINK', 'vcategory-{1}');
}
elseif($mybb->settings['seo'] === 1)
{
	// MyBB style urls
	define('MYDIVS_VIDEO_LINK', 'videos-{1}.html');
	define('MYDIVS_CATEGORY_LINK', 'videos-categories-{1}.html');
}
else
{
	// stock urls
	define('MYDIVS_VIDEO_LINK', 'mydivs.php?action=videos&vid={1}');
	define('MYDIVS_CATEGORY_LINK', 'mydivs.php?action=categories&cid={1}');
}

/* End Hooks */

function myvids_info() {
    global $lang;
    
    $lang->load("myvids");
    
    return array(
        "name" => $lang->myvids_fulltitle,
        "description" => $lang->myvids_description,
        "website" => "http://mybbsource.com",
		"author" => "euantor",
        "authorsite" => "http://forrst.me/euantor",
        "version" => "1.0"
    );
    
}

function myvids_install()
{
    global $db/*, $cache*/, $PL;
	myvids_pluginlibrary_check();

	$db->drop_table('mydivs_videos');
	$db->drop_table('mydivs_comments');
	$db->drop_table('mydivs_categories');

	$collation = $db->build_create_table_collation();
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."mydivs_videos` (
			`vid` int UNSIGNED NOT NULL AUTO_INCREMENT,
			`uid` int NOT NULL DEFAULT '0',
			`cid` int NOT NULL DEFAULT '0',
			`name` varchar(200) NOT NULL DEFAULT '',
			`description` varchar(1000) NOT NULL DEFAULT '',
			`url` varchar(250) NOT NULL DEFAULT '',
			`dateline` int(10) NOT NULL DEFAULT '0',
			`visible` smallint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`vid`),
			UNIQUE KEY (`url`)
		) ENGINE=MyISAM{$collation};"
	);
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."mydivs_comments` (
			`cid` int UNSIGNED NOT NULL AUTO_INCREMENT,
			`uid` int NOT NULL DEFAULT '0',
			`vid` int NOT NULL DEFAULT '0',
			`comment` varchar(500) NOT NULL DEFAULT '',
			`dateline` int(10) NOT NULL DEFAULT '0',
			`visible` smallint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`cid`)
		) ENGINE=MyISAM{$collation};"
	);
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."mydivs_categories` (
			`cid` int UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` varchar(200) NOT NULL DEFAULT '',
			`description` varchar(1000) NOT NULL DEFAULT '',
			`url` varchar(250) NOT NULL DEFAULT '',
			`visible` smallint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`cid`),
			UNIQUE KEY (`url`)
		) ENGINE=MyISAM{$collation};"
	);
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."mydivs_permissions` (
			`gid` int NOT NULL DEFAULT '0',
			`canview` boolean NOT NULL DEFAULT '1',
			`canadd` boolean NOT NULL DEFAULT '1',
			`canupload` boolean NOT NULL DEFAULT '1',
			`cancomment` boolean NOT NULL DEFAULT '1',
			`canedit` boolean NOT NULL DEFAULT '1',
			`candelete` boolean NOT NULL DEFAULT '1',
			`canmod` boolean NOT NULL DEFAULT '1',
			UNIQUE KEY (`gid`)
		) ENGINE=MyISAM{$collation};"
	);
	$db->query('INSERT INTO '.TABLE_PREFIX.'mydivs_permissions
		(gid, canview, canadd, canupload, cancomment, canedit, candelete, canmod)
		VALUES
		(1, 0, 0, 0, 0, 0, 0, 0),
		(2, 1, 1, 0, 1, 1, 1, 0),
		(3, 1, 1, 0, 1, 1, 1, 1),
		(4, 1, 1, 0, 1, 1, 1, 1),
		(5, 0, 0, 0, 0, 0, 0, 0),
		(6, 1, 1, 0, 1, 1, 1, 0),
		(7, 0, 0, 0, 0, 0, 0, 0),
	');
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."mydivs_categories_permissions` (
			`gid` int NOT NULL DEFAULT '0',
			`cid` int NOT NULL DEFAULT '0',
			`canview` boolean NOT NULL DEFAULT '1',
			`canadd` boolean NOT NULL DEFAULT '1',
			`canupload` boolean NOT NULL DEFAULT '1',
			`cancomment` boolean NOT NULL DEFAULT '1',
			`canedit` boolean NOT NULL DEFAULT '1',
			`candelete` boolean NOT NULL DEFAULT '1',
			`canmod` boolean NOT NULL DEFAULT '1',
			UNIQUE KEY (`gid`,`cid`)
		) ENGINE=MyISAM{$collation};"
	);

	$cid = (int)$db->insert_query('videocategories', array(
		'name'			=> $db->escape_string('Default'),
		'description'	=> $db->escape_string('Default category for videos'),
		'url'			=> $db->escape_string('url'),
		'description'	=> $db->escape_string('default'),
	));

	$db->query('INSERT INTO '.TABLE_PREFIX.'mydivs_permissions
		(gid, cid, canview, canadd, canupload, cancomment, canedit, candelete, canmod)
		VALUES
		(1, '.$cid.', 0, 0, 0, 0, 0, 0, 0),
		(2, '.$cid.', 1, 1, 0, 1, 1, 1, 0),
		(3, '.$cid.', 1, 1, 0, 1, 1, 1, 1),
		(4, '.$cid.', 1, 1, 0, 1, 1, 1, 1),
		(5, '.$cid.', 0, 0, 0, 0, 0, 0, 0),
		(6, '.$cid.', 1, 1, 0, 1, 1, 1, 0),
		(7, '.$cid.', 0, 0, 0, 0, 0, 0, 0),
	');

	#$db->add_column('usergroups', '', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'canaddvids', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'cancommentvid', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'needvidmod', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'canmodvid', 'smallint(1) NOT NULL DEFAULT \'0\'');

    // Creates fields for groups-based permissions
	#$db->add_column('usergroups', 'canusemyvids', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'canaddvids', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'cancommentvid', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'needvidmod', 'smallint(1) NOT NULL DEFAULT \'0\'');
	#$db->add_column('usergroups', 'canmodvid', 'smallint(1) NOT NULL DEFAULT \'0\'');

    // Set permissions
	#$db->update_query('usergroups', array('canusemyvids' => 1, 'canaddvids' => 1));
	#$db->update_query('usergroups', array('cancommentvid' => 1), 'gid IN (2, 3, 4, 6)');
	#$db->update_query('usergroups', array('canmodvid' => 1), 'gid IN (3, 4, 6)');
	
    #$cache->update_usergroups();
}

function myvids_is_installed()
{
    global $db;

	return $db->table_exists('mydivs_videos');
}

function myvids_activate()
{
    global $db, $mybb, $lang, $theme, $templates, $PL;
	myvids_pluginlibrary_check();

	// Add our settings
	$PL->settings('myvids', 'MyVids', 'Setting group for the MyVids plugin.', array(
		'seo'	=> array(
			'title'			=> 'Enable Search Engine Friendly URLs?',
			'description'	=> 'Search engine friendly URLs change the MyVids links to shorter URLs which search engines prefer and are easier to type. <strong>Once this setting is enabled you need to make sure you have the MyBB .htaccess in your MyBB root directory (or the equivalent for your web server). Automatic detection may not work on all servers.</strong>',
			'optionscode'	=> 'select
2=Google SEO
1=MyBB
0=Dynamic',
			'value'			=> 0,
		),
		'upload'	=> array(
			'title'			=> 'Allow the uploading of video files?',
			'description'	=> 'Do you wish to allow users to upload their own files to your server? This can cause problems if you have a lot of users uploading a lot of videos and/or watching a lot of videos. Check the documentation for more info.',
			'optionscode'	=> 'yesno',
			'value'			=> 0,
		),
		'uploadpath'	=> array(
			'title'			=> 'Location of uplaoded files?',
			'description'	=> 'Where should uploaded video files be stored? Please follow the default\'s example of structure.',
			'optionscode'	=> 'text',
			'value'			=> './uploads/videos',
		),
		'minpostleg'	=> array(
			'title'			=> 'Minimum description/comment length?',
			'description'	=> 'Minimum comment length is used in an attempt to deter spam. 10 is a good minimum value for msot boards.',
			'optionscode'	=> 'text',
			'value'			=> 10,
		),
		'flood'	=> array(
			'title'			=> 'Check time flood?',
			'description'	=> 'Enter the minimum of seconds users must wait before sending multiple videos/comments. Empty to disable.',
			'optionscode'	=> '',
			'value'			=> 0,
		),
	));

	// Insert template/group
	$PL->templates('myvids', 'MyVids', array(
		'index'	=> '{$headerinclude}
    <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->myvids_base}</title>
{$header}
	<div class="float_left">
		<form action="searchmyvids.php" method="post">
			<input type="text" name="searchterm" class="textbox" /> <input type="submit" name="submit" value="{$lang->myvids_search}" />
		</form>
	</div>
    {$addbutton}
    <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
        <thead>
            <tr>
                <td class="thead" colspan="2"><strong>{$lang->myvids_latestvideos}</strong></td>
            </tr>
            <tr>
                <td class="tcat">{$lang->myvids_name}</td>
                <td class="tcat">{$lang->myvids_description}</td>
            </tr>
        </thead>
        <tbody>
            {$latestvideolist}
        </tbody>
    </table>
	<div class="float_right">
		{$multipage}
	</div>
	<br style="clear: both;" />
{$footer}',
		'watch'	=> '{$headerinclude}
    <title>{$lang->myvids_watching_vid} "{$vidtitle}" - {$mybb->settings[\\\'bbname\\\']}</title>
    <script type="text/javascript" src="{$mybb->settings[\\\'bburl\\\']}/jscripts/jquery.min.js"></script> 
	
	<script type="text/javascript">
	jQuery.noConflict();
	jQuery(document).ready(function($) {

		var pages = $(".pagination_page");

		pages.click(function() {

			var pageNum = this.id;
			var targetUrl = "videocomments.php?vid={$vid}&page=" + pageNum;

			$(".pagination_current").removeClass("pagination_current");
			$(this).addClass("pagination_current");

			$.get(targetUrl, function(data) {
				$(".maincommentlist").html(data);
			});

			var destination = $(".maincommentlist").offset().top;
			$("html:not(:animated),body:not(:animated)").animate({
				scrollTop: destination
			}, 500);
			return false;
		});

		$(".editcomment").live("click", function() {
			event.preventDefault();
			var id = this.id.substr(4);

			$(".commentval" + id).hide();
			$(".editform" + id).show();

			$(".editform" + id + "form").submit(function() {

				$.get("myvids_modify.php?type=c&vcid=" + id, {
					commentval: $("#newcommentval" + id).val(), method: "js"
				}, function(data) {

					if (data === "false" || data === "" || data === "{$lang->myvids_commenterror}") {
						$(".editform" + id).hide();
						$(".commentval" + id).show();
						if (data === "{$lang->myvids_commenterror}") {
							alert("{$lang->myvids_commenterror}");
						}
					}
					else {
						$(".editform" + id).hide();
						$(".commentval" + id).html(data);
						$(".commentval" + id).show();
					}
				});

				return false;
			});

			$("input[name=canceledit" + id + "]").live("click", function() {
				$(".editform" + id).hide();
				$(".commentval" + id).show();
			});
		});
		
		$(".delcomment").live("click", function() {
			event.preventDefault();
			var id = this.id.substr(6);
			
			$.get("myvids_modify.php?type=c&action=delete&vcid=" + id, {method: "js"}, function(data) {

				if (data === "deleted") {
					$("#comment" + id).remove();
				}
				else {
						
				}
			});
			
		});
	});
	</script>
</head>
<body>
{$header}
    <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
        <thead>
            <tr>
                <td class="thead" colspan="2"><strong>{$vidtitle}</strong></td>
            </tr>
        </thead>
            <tr>
                <td class="trow1" style="text-align: center;" colspan="2">
                    {$video}
                    
                    <fieldset style="text-align: left;">
                        <legend>{$lang->myvids_videodetails}</legend>
                            <strong>{$lang->myvids_addedby}</strong> <a href="member.php?action=profile&amp;uid={$userid}">{$user}</a><br />
                            <strong>{$lang->myvids_dateadded}</strong> {$videodate} at {$videotime}<br />
			    <strong>{$lang->myvids_vidcategory}</strong> <a href="myvidcategories.php?cat={$catid}">{$catname}</a>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td class="tcat" colspan="2"><strong>{$lang->myvids_video_desc}</strong></td>
            </tr>
            <tr>
                <td class="trow2" colspan="2">
                    {$viddesc}
                </td>
            </tr>
            <tr>
                <td class="tcat" colspan="2"><strong>{$lang->myvids_video_comments}</strong></td>
            </tr>
                {$addcomment}
			<tbody class="maincommentlist">
                {$videocomments}
			</tbody>
            {$pagination}
    </table>
{$footer}',
		'singlecomment'	=> '<tr id="comment{$comment[\\\'vcid\\\']}">
            <td class="{$bgcolor}" width="70" align="center">
                <a href="member.php?action=profile&amp;uid={$comment[\\\'uid\\\']}">{$comment[\\\'avatar\\\']}</a>
               <strong><a href="member.php?action=profile&amp;uid={$comment[\\\'uid\\\']}">{$comment[\\\'formattedname\\\']}</a></strong>
            </td>
            <td class="{$bgcolor}">
                    <p class="commentval{$comment[\\\'vcid\\\']}">{$comment[\\\'vidcomment\\\']}</p>
					{$editform}
					<div class="{$bgcolor} smalltext" style="text-align: left">
						{$comment[\\\'commenttime\\\']}{$editbar}
						<div style="clear: both;"></div>
					</div>
            </td>
        </tr>',
		'addcomment'	=> '<tr>
    <td class="trow1" colspan="2">
        <fieldset>
            <legend>{$lang->myvids_addcomment}</legend>
            <form action="addvidcomment.php?vid={$vid}" method="post" id="addcomment">
                <textarea name="comment" style="width: 298px; height: 92px" class="commenttext"></textarea><br />
                <input type="submit" name="submit" value="{$lang->myvids_addcomment}" class="button" />
            </form>
        </fieldset>
    </td>
</tr>

<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {

	$("#addcomment").submit(function() {

		var comment = $(".commenttext").val();
		var uid = $(".commentuid").val();

		$.post("addvidcomment.php?vid={$vid}", { comment: comment, method: "js" }, function(data) {
			$(".commenttext").val("");
			
			if (data === "{$lang->myvids_commenterror}") {
				alert("{$lang->myvids_commenterror}");
			}
		});

		var targetUrl = "videocomments.php?vid={$vid}&page=1";
			
		$.get(targetUrl, function(data) {
			$(".maincommentlist").html(data);
		});

		return false;
	});
});
</script>',
		'add_button'	=> '    <div class="float_right">
        <a href="addvid.php"><img src="{$theme[\\\'imgdir\\\']}/english/addvideo.png" alt=""></a>
    </div>
    <br class="clear" />
    <br />',
		'addvid'	=> '{$headerinclude}
    <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->myvids_addvid}</title>
    <script type="text/javascript" src="{$mybb->settings[\\\'bburl\\\']}/jscripts/jquery.min.js"></script> 

<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {

    $("input[name=\\\'vidtype\\\']").change(function() {
        if ($("input[name=\\\'vidtype\\\']:checked").val() == \\\'upload\\\') {
            $("#embed").hide();
            $("#upload").show();
        }
        else {
            $("#upload").hide();
            $("#embed").show();
        }
    });
});
</script>
{$header}
    <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
        <thead>
            <tr>
                <td class="thead" colspan="2"><strong>{$lang->myvids_addvid}</strong></td>
            </tr>
        </thead>
        <tbody>
            <form action="addvid.php?action=add" method="post" enctype="multipart/form-data">
                <tr>
                    <td class="trow1 smalltext" width="20%"><label for="vidname">{$lang->myvids_vidname}</label></td>
                    <td class="trow1">
                        <input class="textbox" type="text" name="vidname" style="width: 95%" />
                    </td>
                </tr>
				{$vidupload}
                <tr>
                    <td class="trow2 smalltext"><label for="vidurl">{$lang->myvids_category}</label></td>
                    <td class="trow2">
                        {$categories}
                    </td>
                </tr>
                <tr>
                    <td class="trow1 smalltext"><label for="viddesc">{$lang->myvids_video_desc}</label></td>
                    <td class="trow1">
                        <textarea name="viddesc" style="width: 95%" rows="10"></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="trow2" aligh="left">
                        <input type="submit" value="{$lang->myvids_addvid}" />
                    </td>
                </tr>
            </form>
        </tbody>
    </table>
{$footer}',
		'editbar'	=> '<span style="text-align: right; float: right;">
	<a href="myvids_modify.php?type=c&amp;vcid={$comment[\\\'vcid\\\']}" class="editcomment" id="edit{$comment[\\\'vcid\\\']}">{$lang->myvids_edit_comment}</a> | <a href="myvids_modify.php?type=c&amp;action=delete&amp;vcid={$comment[\\\'vcid\\\']}" class="delcomment" id="delete{$comment[\\\'vcid\\\']}">{$lang->myvids_delete_comment}</a></strong>
</span>',
		'nocomments'	=> '<tr>
	<td class="trow1" colspan="2" align="center">
		<strong>{$lang->myvids_nocomments}</strong>
	</td>
</tr>',
		'commentpagination'	=> '<tr>
	<td colspan="2" class="trow1">
        <div class="pagination">{$paging}</ul>
	</td>
</tr>',
		'fulledit'	=> '{$headerinclude}
    <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->myvids_edit_comment}</title>
{$header}
    <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
        <thead>
            <tr>
                <td class="thead" colspan="2"><strong>{$lang->myvids_edit_comment}</strong></td>
            </tr>
        </thead>
        <tbody>
            <form action="myvids_modify.php?type=c&vcid={$vcid}&next=doedit" method="post">
                <tr>
                    <td class="trow1 smalltext"><textarea name="commentval" style="width: 100%;">{$currentcomment[\\\'vidcomment\\\']}</textarea></td>
                </tr>
                <tr>
                    <td  class="trow2" aligh="left">
                        <input type="hidden" name="redirect_url" value="{$redirect_url}" />
                        <input type="submit" value="{$lang->myvids_edit_comment}" />
                    </td>
                </tr>
            </form>
        </tbody>
    </table>
{$footer}',
	));
}

function myvids_deactivate()
{
}

function myvids_uninstall()
{
	global $db/*, $cache*/, $PL;
	myvids_pluginlibrary_check();

	$db->drop_table('mydivs_videos');
	$db->drop_table('mydivs_comments');
	$db->drop_table('mydivs_categories');

	#$db->drop_column('usergroups', 'canusemyvids');
	#$db->drop_column('usergroups', 'canaddvids');
	#$db->drop_column('usergroups', 'cancommentvid');
	#$db->drop_column('usergroups', 'needvidmod');
	#$db->drop_column('usergroups', 'canmodvid');

	$PL->settings_delete('myvids');

	$PL->templates_delete('myvids');

    #$cache->update_usergroups();
}

function myvids_pluginlibrary_check()
{
	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message('You need PluginLibrary to run this plugin.', 'error');
		admin_redirect('index.php?module=config-plugins');
	}

	global $PL;
	$PL or require_once PLUGINLIBRARY;

	if($PL->version < 11)
	{
		flash_message('Your PluginLibrary is to old to run this plugin.', 'error');
		admin_redirect('index.php?module=config-plugins');
	}
}

function myvids_profofile() 
{
	global $db, $memprofile;

	$where = 'uid=\''.(int)$memprofile['uid'].'\'';
	if(!$mybb->user['ismoderator'])
	{
		$where = ' AND visible=\'1\'';
	}

	$query = $db->simple_select('mydivs_videos', 'COUNT(vid) as videos', $where);
	$memprofile['videos'] = (int)$db->fetch_field($query, 'videos');
	$query = $db->simple_select('mydivs_comments', 'COUNT(cid) as comments', $where);
	$memprofile['videocomments'] = (int)$db->fetch_field($query, 'comments');
		
}

function myvids_search()
{
	// TODO: Search comments
	// TODO: WHERE clause should exclude unviable categories by current user
	if($mybb->input['action'] == 'videos')
	{
		global $mybb, $db, $lang, $theme;

		$plugins->run_hooks('myvids_search_start');	

		if (!$mybb->usergroup['canusemyvids']) {
			error_no_permission();
		}

		$lang->load("myvids");

		add_breadcrumb($lang->myvids_home, "myvids.php");
		add_breadcrumb($lang->myvids_search, "searchmyvids.php");

		$searchterm = clean_keywords($db->escape_string(trim($mybb->input['searchterm'])));

		if (strlen($searchterm) < $mybb->settings['minsearchword']) {
			$lang->error_minsearchlength = $lang->sprintf($lang->error_minsearchlength, $mybb->settings['minsearchword']);
			error($lang->error_minsearchlength);
		}

		$where = '(name LIKE \'%'.$searchterm.'%\' OR description LIKE \'%'.$searchterm.'%\')';
		if(!$mybb->user['ismoderator'])
		{
			$where = ' AND visible=\'1\'';
		}

		$numvid = $db->fetch_field($db->simple_select("myvids_videos", "COUNT(vid) as count", $where), "count");

		$page = intval($mybb->input['page']);

		if($page < 1) {
			$page = 1;
		}

		$multipage = multipage($numvid, 10, $page, $_SERVER['PHP_SELF']);

		$searchresults = $db->simple_select("myvids_videos", "*", $where, array("order_by" => 'ABS(viddate)', "order_dir" => 'DESC', "limit" => (($page-1)*10).", 10"));

		if (!$db->num_rows($searchresults) ) {
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

		$plugins->run_hooks('myvids_search_end');	

		eval('$page = "'.$templates->get('myvids_index').'";');
		output_page($page);
		exit;
	}
}