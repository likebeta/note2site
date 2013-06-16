<?php
$path = isset($_GET['path']) ? $_GET['path']:'';
if ($path === '')
{
	include('../404.php');
	exit;
}

// 开始获取note内容
require_once('../config.php');
require('../oauth/ynote_client.php');
require('../oauth/ynote_parse.php');

$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
$response = $client->getNote($oauth_access_token,$oauth_access_secret,$path);

if ($note = parseNote($response)){
	if (preg_match_all('/<img.*?\s+src=\"(.+?)\".*?\sdata-media-type=\"image\".*?>/',$note->content,$out) && $out[0] !== '')
	{
		$imgurls = $out[1];
		$imgs = array();
		foreach($out[1] as $img){
			$imgurl = $client->getAuthorizedDownloadLink($oauth_access_token, $oauth_access_secret, $img);
			$imgs[] = $imgurl;
		}
		$img_obj = new preg_image_class($imgurls,$imgs);
		$note->content = preg_replace_callback('/<img.*?\s+src=\"(.+?)\".*?\sdata-media-type=\"image\".*?>/',array(&$img_obj,'preg_callback'),$note->content);
	}

	if(preg_match_all('/<img.*?\s+src=\"(.+?)\".+?\s+filename=\"(.+?)\".*?\s+path=\"(.+?)\".*?data-media-type=\"attachment\".*?>/',$note->content,$out) && $out[0] !== ''){
		$imgurls = $out[1];
		$titles = $out[2];
		$attachmenturls = $out[3];
		$imgs = array();
		$attachments = array();
		for($i = 0; $i < count($imgurls);$i++){
			$imgurl = $client->getAuthorizedDownloadLink($oauth_access_token, $oauth_access_secret, $imgurls[$i]);
			$attachment = $client->getAuthorizedDownloadLink($oauth_access_token, $oauth_access_secret, $attachmenturls[$i]);
			$imgs[] = $imgurl;
			$attachments[] = $attachment; 
		}
		$attachment_obj = new preg_attachment_class($imgurls,$imgs,$titles,$attachmenturls,$attachments);
		$note->content = preg_replace_callback('/<img.*?\s+src=\"(.+?)\".+?\s+filename=\"(.+?)\".*?\s+path=\"(.+?)\".*?data-media-type=\"attachment\".*?>/',array(&$attachment_obj,'preg_callback'),$note->content);
	}
}
else{
	die('网页不存在或者服务器错误');
}
?>
<!doctype html>
<html>
<head>
<title>archive | <?php echo $note->title;?></title>
<meta charset="utf8" />
</head>
<body>
<?php
	echo $note->content;
?>
</body>
</html>

<?php
class preg_image_class{
	private $imgurls;
	private $imgs;
	function __construct($imgurls,$imgs){
		$this->imgurls = $imgurls;
		$this->imgs = $imgs;
	}

	function preg_callback($matchs){
		for($i = 0; $i < count($this->imgurls); ++$i){
			if ($matchs[1] === $this->imgurls[$i]){
				return str_replace($matchs[1],$this->imgs[$i],$matchs[0]);
			}
		}
	}
}
class preg_attachment_class{
    private $imgurls;
    private $imgs;
	private $titles;
	private $attachmenturls;
	private $attachments;
    function __construct($imgurls,$imgs,$titles,$attachmenturls,$attachments){
       $this->imgurls = $imgurls;
       $this->imgs = $imgs;
	   $this->titles = $titles;
	   $this->attachmenturls = $attachmenturls;
	   $this->attachments = $attachments;
	 }

	 function preg_callback($matchs){
		for($i = 0; $i < count($this->imgurls); ++$i){
			if ($matchs[1] === $this->imgurls[$i]){
				return '<a href="'.$this->attachments[$i].'" title="'.$this->titles[$i].'"><img src="'.$this->imgs[$i].'" /></a>';
			}
		}
	}
}
?>
