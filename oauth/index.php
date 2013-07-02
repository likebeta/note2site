<!doctype html>
<html>
<head>
<title>note2site</title>
<meta charset="utf-8" />
</head>
<body>
<?php
	function write_file($name,$content){
		$file = fopen('/tmp/www/'.$name.'.html','w+');
			if ($file){
			fwrite($file,"<!doctype>\n<html>\n<head><title>$name</title><meta charset=\"utf8\" /></head>\n<body>\n".$content."</body>\n</html>");
			fclose($file);
		}
	}
?>
<?php
require('../config.php');
require('ynote_client.php');
require('ynote_parse.php');

	$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);

	$notes = array('/V5_B_BU/0175E4E030CF431CAAD60AC703B4B146','/8E00330A442B49688B2ECEFE3E9A7FBA/55E1FFBB77574591B51A17FCAC0CA874');
	foreach($notes as $notePath){
		$get_note_response = $client->getNote($oauth_access_token, $oauth_access_secret, $notePath);
		$note = parseNote($get_note_response);
		echo $note->title.':'.$note->path.'<br />';
		preg_match_all('/<img.*?\s+src=\"(.+?)\".*?\sdata-media-type=\"image\".*?>/',$note->content,$out);
		$imgurls = $out[1];
		$imgs = array();
		foreach($out[1] as $img){
			$imgurl = $client->getAuthorizedDownloadLink($oauth_access_token, $oauth_access_secret, $img);
			$imgs[] = $imgurl;
		}
		$img_obj = new preg_image_class($imgurls,$imgs);
		$note->content = preg_replace_callback('/<img.*?\s+src=\"(.+?)\".*?\sdata-media-type=\"image\".*?>/',array(&$img_obj,'preg_callback'),$note->content);
		if(preg_match_all('/<img.*?\s+src=\"(.+?)\".+?\s+filename=\"(.+?)\".*?\s+path=\"(.+?)\".*?data-media-type=\"attachment\".*?>/',$note->content,$out)){
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
		write_file($note->title,$note->content);
	}


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
</body>
</html>
