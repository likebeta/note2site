<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="description" content=""> 
<meta name="keywords" content="">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
<title>note2site</title>
<link rel="stylesheet" href="<?php echo SITE_URL;?>/static/css/style.css">
</head>
<body>
    <div class="doc" id="doc">
        <div class="header" id="header">
            <div class="logo"><a href="<?php echo SITE_URL;?>" title="note2site" style="color:#fff">note2site</a></div>
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
	foreach ($notebooks as $key => $notebook)
	{
		if ($key == 0)
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
                    <div class="main-wrap" id="main-wrap">
                        <div class="note-list-wrap publicpage-note-list not-login" id="note-list-wrap" style="top: 10px;">
                            <table class="mod-note-list" id="mod-note-list">
                                <colgroup><col class="t4"><col class="t6"></colgroup>
                                <tbody class="note-list-body">
<?php
	$str_echo = '';
	foreach ($notes as $note) {
	$str_echo .= <<<EOF
                                    <tr class="note-list-item" id="">
                                        <td class="t4">
                                            <div class="note-title public-note-title">
                                                <a class="note-title-con" href="{$site_url}/archive{$note->path}">{$note->title}</a>
                                            </div>
                                            <div class="note-preview public-note-preview">
                                                <span class="public-note-con"></span>
                                            </div>
                                        </td>
                                        <td class="t6">
                                            <em class="time">{$note->create_time}</em>
                                        </td>
                                    </tr>
EOF;
	}
	echo $str_echo;
?>                                	

                                </tbody>
                            </table>
                            <div class="pager bottom-pager"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var trs = document.getElementsByTagName('tr');
        if (trs)
        {
            for (var i = trs.length - 1; i >= 0; i--)
            {
                addHandler(trs[i],'click',handle_click);
            }
        }

        function handle_click(event)
        {
           for (var i = 0; i < document.links.length; i++)
           {
               if (isParent(document.links[i],this))
               {
                    location.href = document.links[i];
                    break;
               }
           }
        }

        function addHandler(ele,event,func)
        {
            if (ele.addEventListener)
            {
                ele.addEventListener(event, func, false);
            }
            else if (ele.attachEvent)
            {
                ele.attachEvent('on' + event, func);
            }
            else
            {
                ele['on' + event] = func;
            }
        }

        function isParent (obj,parentObj){            
             while (obj != undefined && obj != null && obj.tagName.toLowerCase() != 'body'){            
                 if (obj == parentObj){            
                     return true;            
                 }            
                 obj = obj.parentNode;            
             }            
             return false;            
        }

    </script>
</body>
</html>