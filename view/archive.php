<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="description" content=""> 
<meta name="keywords" content="">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
<title><?php echo $note->title;?></title>
<link rel="stylesheet" href="<?php echo SITE_URL;?>/static/css/style.css">
</head>
<body>  
    <div class="doc" id="doc">
        <div class="header" id="header">
            <div class="logo"><a href="<?php echo SITE_URL;?>" title="note2site" target="_self">note2site</a></div>
            <div class="links">
                <a href="https://github.com/likebeta/note2site">github</a>
            </div>
        </div>
        <div class="content" id="content">
            <div class="mod-left-side public-page" id="mod-left-side">
                <div class="mod-tree" id="mod-tree">
                    <div class="tree-wrap">
                        <div class="t-trunk">
                            <div class="t-branch">
                                <div class="t-panel">
                                    <div class="t-title note-title-block has-children expanded" id="my-note-area">
                                        <em>likebeta的共享笔记</em>
                                    </div>
                                    <ul class="t-folder note-content-block" id="categories" style="display:block;">
                                        <li>
                                            <div class="public-notes">
                                                <ul class="t-folder" style="display:block;" id="public-notes-con">
<?php
	$str_echo = '';
	$site_url = SITE_URL;
	foreach ($notebooks as $notebook)
	{
		if ($notebook->path == $this_notebook->path)
		{
				$str_echo .= <<<EOF
                                                    <li class="t-cate">
                                                        <div class="t-title selected note-default-cate">
                                                            <a class="t-folder-icon" title="{$notebook->name}" href="{$site_url}/topic{$notebook->path}">
                                                                <em class="txt">{$notebook->name}</em>
                                                                <em class="num">({$notebook->notes_num})</em>
                                                            </a>
                                                        </div>
                                                    </li>													
EOF;
	}
		else
		{
				$str_echo .= <<<EOF
                                                    <li class="t-cate">
                                                        <div class="t-title">
                                                            <a class="t-folder-icon" title="{$notebook->name}" href="{$site_url}/topic{$notebook->path}">
                                                                <em class="txt">{$notebook->name}</em>
                                                                <em class="num">({$notebook->notes_num})</em>
                                                            </a>
                                                        </div>
                                                    </li>													
EOF;
	}

	}
	echo $str_echo;
?>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mod-public-ads"></div>
            <div class="mod-right-side public-page" id="mod-right-side">
                <div class="mod-main" id="mod-main">
                    <div class="main-wrap" id="main-wrap" style="margin-top:10px;">
                        <div class="right-top-wrap" id="right-top-wrap">
                            <div class="mod-note-title publicpage">
                                <h2><?php echo $note->title;?></h2>
                             </div>
                        </div>
                        <div class="mod-note-body publicpage-note-preview" id="note-list-wrap" style="top:50px;overflow-y:auto">
                            <div class="note-info hide" id="note-info">
                                <p class="detail-line time-line">
                                    <em class="create-time"><em class="label">创建时间：</em><?php echo $note->create_time;?></em>
                                    <em class="modify-time">修改时间： <?php echo $note->modify_time;?></em>
                                </p>        
                            </div>
                            <div class="inote-editor mod-note-content" id="mod-note-content" height="100%">
                                <div id="note-content-wrap" style="font:16px/1.5 宋体">
                                    <?php echo $note->content;?>
                                </div>
                                <div class="mod-note-comment" style="display: none">
                                    <div class="note-comment-title"><h2>用户评论</h2></div>
                                    <div class="note-comment-body" id="note-comment-body">
                                        <div class="note-comment-empty" id="note-comment-empty">暂无评论。</div>  
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>