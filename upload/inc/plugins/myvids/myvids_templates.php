<?php
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function create_myvids_templates() {
    global $mybb, $db, $templates, $theme;
    
    $template[0] = array(
		"title" => 'myvids_index',
		"template"	=> '{$headerinclude}
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
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
    
    $template[1] = array(
		"title" => 'myvids_watch',
		"template"	=> '{$headerinclude}
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
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
    
    $template[2] = array(
		"title" => 'myvids_singlecomment',
		"template"	=> '<tr id="comment{$comment[\\\'vcid\\\']}">
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
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
    
    $template[3] = array(
		"title" => 'myvids_addcomment',
		"template"	=> '<tr>
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
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);

    $template[4] = array(
		"title" => 'myvids_add_button',
		"template"	=> '    <div class="float_right">
        <a href="addvid.php"><img src="{$theme[\\\'imgdir\\\']}/english/addvideo.png" alt=""></a>
    </div>
    <br class="clear" />
    <br />',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
    
    $template[5] = array(
		"title" => 'myvids_addvid',
		"template"	=> '{$headerinclude}
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
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
    
    $template[6] = array(
		"title" => 'myvids_editbar',
		"template"	=> '<span style="text-align: right; float: right;">
	<a href="myvids_modify.php?type=c&amp;vcid={$comment[\\\'vcid\\\']}" class="editcomment" id="edit{$comment[\\\'vcid\\\']}">{$lang->myvids_edit_comment}</a> | <a href="myvids_modify.php?type=c&amp;action=delete&amp;vcid={$comment[\\\'vcid\\\']}" class="delcomment" id="delete{$comment[\\\'vcid\\\']}">{$lang->myvids_delete_comment}</a></strong>
</span>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
    $template[7] = array(
		"title" => 'myvids_nocomments',
		"template"	=> '<tr>
	<td class="trow1" colspan="2" align="center">
		<strong>{$lang->myvids_nocomments}</strong>
	</td>
</tr>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
    $template[8] = array(
		"title" => 'myvids_commentpagination',
		"template"	=> '<tr>
	<td colspan="2" class="trow1">
        <div class="pagination">{$paging}</ul>
	</td>
</tr>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
    $template[9] = array(
		"title" => 'myvids_fulledit',
		"template"	=> '{$headerinclude}
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
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);

	foreach ($template as $row) {
		$db->insert_query("templates", $row);
	}
}
?>
