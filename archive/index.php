<?php
$path = isset($_GET['path']) ? $_GET['path']:'';
if ($path === '')
{
	include('../404.php');
	exit;
}

// get content of the note
require_once('../config.php');
require('../oauth/ynote_client.php');
require('../oauth/ynote_parse.php');

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($mysqli->connect_errno) {
	$msg = "Connect failed: ".$mysqli->connect_error;
	die($msg);
}

$notebook_title = '';
$notebook_path = '';
$strsql = "SELECT notebook.name,notebook.path FROM notebook WHERE notebook.path=(SELECT note.notebook_path FROM note WHERE note.path='/".$path."')";
if ($results = $mysqli->query($strsql)) {
	if ($results->num_rows !== 1) {
		include('../404.php');
		exit();
	}
	$row = $results->fetch_assoc();
	$notebook_title = $row['name'];
	$notebook_path = $row['path'];
}
$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
$response = $client->getNote($oauth_access_token,$oauth_access_secret,$path);

if ($note = parseNote($response)){
	if (preg_match_all('/<img.*?\s+data-media-type=\"image\".*?\ssrc=\"(.+?)\".*?>|<img.*?\s+src=\"(.+?)\".*?\sdata-media-type=\"image\".*?>/',$note->content,$out) && $out[0] !== '')
	{
		$imgurls = $out[1];
		$imgs = array();
		foreach($out[1] as $img){
			$imgurl = $client->getAuthorizedDownloadLink($oauth_access_token, $oauth_access_secret, $img);
			$imgs[] = $imgurl;
		}
		$img_obj = new preg_image_class($imgurls,$imgs);
		$note->content = preg_replace_callback('/<img.*?\s+data-media-type=\"image\".*?\ssrc=\"(.+?)\".*?>|<img.*?\s+src=\"(.+?)\".*?\sdata-media-type=\"image\".*?>/',array(&$img_obj,'preg_callback'),$note->content);
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
	die('This page no longer exists or server error');
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $note->title;?> | note2site</title>
<meta charset="utf8" />
</head>
<body>
<?php
	echo "<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$site_url\" title=\"home\">home</a> &gt; <a href=\"$site_url/topic".$notebook_path.".html\" title=\"$notebook_title\">$notebook_title</a> &gt; ".$note->title."</div>";
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
