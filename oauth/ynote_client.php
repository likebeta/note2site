<?php
/**
 * @(#)YNoteClient.java, 2012-2-27. 
 * 
 * Copyright 2012 Yodao, Inc. All rights reserved.
 * YODAO PROPRIETARY/CONFIDENTIAL. Use is subject to license terms.
 */

require_once 'HttpClient.class.php';

/*
	YNote client which is used to access YNote data via the open API. For a
	consumer application, each YNote client instance is supposed to associated
	with a single user. And the YNote client is able to access the user's data
	if and only if the user has granted the authorization to this consumer.

    Note that although this YNoteClient could store access token and secret,
    if is strongly recommended that the application maintain these information
    itself. So all the operation have the parameter $oauth_access_token and
    $oauth_access_secret.
	
	Please put the HttpClient.class.php file along with this file, and enable
	curl extension of PHP in order to make this SDK work properly.
	
	by jiaru@rd.netease.com
*/
class YnoteClient {
// base url ---------------------------------------------------------------
	protected $url_base = null;

// client var -------------------------------------------------------------	
	protected $oauth_signature_method = "HMAC-SHA1";
	protected $oauth_version = "1.0";

	protected $oauth_consumer_key = NULL;
	protected $oauth_consumer_secret = NULL;
	protected $oauth_request_token = NULL;
	protected $oauth_request_secret = NULL;
	protected $oauth_access_token = NULL;
	protected $oauth_access_secret = NULL;
	protected $oauth_verifier = NULL;

	// verified
	/* Construct a YNote client for a given consumer. It is supposed to construct
		a YNoteClient instance for each user in an application.
		$url_base: the base url of the YNote open api.
	*/
	function __construct ($oauth_consumer_key, $oauth_consumer_secret, $url_base = "http://note.youdao.com") {
		$this->oauth_consumer_key = $oauth_consumer_key;
		$this->oauth_consumer_secret = $oauth_consumer_secret;
        $this->url_base = $url_base;
	}

/************************************************************************************/

    // verified
	/* Grant the access token and secret for this consumer based on the authorized request 
		token and secret. The consumer could access the user's data in YNote after the access
		token is granted.
		User must have granted the authorization for this consumer before this method is invoked.
		Usually this method is invoked in a callback method which is notified after user authorized.
	*/
	function getAccessToken($oauth_request_token, $oauth_request_secret, $oauth_verifier) {
		$request_url = $this->url_base."/oauth/access_token";
		$request_params = $this->generateOAuthParams($oauth_request_token, $oauth_verifier);
		$base_string = $this->buildBaseString('GET', $request_url, $request_params);
		$oauth_signature = $this->sign($base_string, $oauth_request_secret);
		$response = $this->doSignedGetRequest($request_url, $request_params, $oauth_signature);
        	$parsed_response = $this->parseTokenResponse($response);
	        $this->oauth_access_token = $parsed_response['oauth_token'];
        	$this->oauth_access_secret = $parsed_response['oauth_token_secret'];
		return $parsed_response;
	}

	// verified
	/* Get the authorize URL. The authorize URL is used for the user to grant the 
		authorization for this consumer. Note that the authorizing reqeust token
        is the requset token given by the $oauth_request_token of  this YnoteClient
        object, make sure to call getRequestToken before this method is invoked.
		$call_bakc_url: the call back url after the authorization.
		return: the authorize URL.
	*/
	function getAuthorizeUrl($call_bakc_url = '') {
		$request_url = $this->url_base."/oauth/authorize";
		$request_url .= "?oauth_token=".$this->oauth_request_token;
		if ($call_bakc_url != '') {
			$request_url .= "&oauth_callback=".rawurlencode($call_bakc_url);
		}
		return $request_url;
	}

	// verified
	/* Get the OAuth request token and secret for this consumer based on the
		consumer key and consumer secret.
		return: an array which contains both the request token and secret.
	*/
	function getRequestToken() {
		$request_url = $this->url_base."/oauth/request_token";
		$request_params = $this->generateOAuthParams();
		$base_string = $this->buildBaseString('GET', $request_url, $request_params);
		$oauth_signature = $this->sign($base_string);
		$response = $this->doSignedGetRequest($request_url, $request_params, $oauth_signature);
		$parsed_response = $this->parseTokenResponse($response);
		$this->oauth_request_token = $parsed_response['oauth_token'];
		$this->oauth_request_secret = $parsed_response['oauth_token_secret'];
		return $parsed_response;
	}

    // verified
	/* Get the authorized download link of the given resource path.
		$download_path: the original download path of the resource.
		(e.g. "http://note.youdao.com/yws/open/resource/21/7f2bd6fa2795441c")
		return: the authorized download link which could be used to access the resource.
	*/
    function getAuthorizedDownloadLink($oauth_access_token, $oauth_access_secret, $download_path) {
        $request_url = $download_path;
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $base_string = $this->buildBaseString('GET', $request_url, $request_params);
		$oauth_signature = $this->sign($base_string, $oauth_access_secret);
		
        $request_url_full = $request_url;
		if (count($request_params) > 0) {
			$request_params_string = $this->buildParamString($request_params);
			$request_url_full .= '?'.$request_params_string;
		}
		if (isset($oauth_signature)) {
			$request_url_full.='&oauth_signature='.$oauth_signature;
		}
		return $request_url_full;
    }

    // verified
	/* Upload an attachment.
		$server_file_name: file path of the uploading file.
		An "@" will be added infront of the file path to inform the server to upload the
		file immediately.
		return: the URL of the uploaded file.
		(example 
			for the images, since the image could used as icon itself, returned infomation
			will be like 
				{"url" : "http://baseURL/yws/open/resource/28/d83f547f3055"}
			for other kinds of file, returned information will contain the icon URL and
			resource URL, like
				{
				"url" : "http://baseURL/yws/open/resource/28/d83f547f3055"	  // attachment URL
				"src": " http://baseURL/yws/open/resource/29/7f2bd6fa2795"	  // icon URL
				}
		)
		
		Pay attention: the attachment information must be updated into the corresponding
		note, or the attachment will be removed by the space recycling if it is not involved
		in any note.
	*/
    function uploadAttachment($oauth_access_token, $oauth_access_secret, $server_file_name) {
        $request_url = $this->url_base."/yws/open/resource/upload.json";
        
        $params = $this->generateOAuthParams($oauth_access_token);
        $base_string = $this->buildBaseString('POST', $request_url, $params);

	    $oauth_signature_key = urlencode($this->oauth_consumer_secret).'&'.urlencode($oauth_access_secret);
	    $oauth_signature = base64_encode(hash_hmac('sha1', $base_string, $oauth_signature_key, true));

        $response = $this->doSignedMultipartPostRequest($request_url, $params, $oauth_signature, array('file' => "@".$server_file_name));
        return $response;
    }

    // verified
	/* Update the note.
		$noteinfo the new information of the note in array, like
			array(	'source'	=> 'http://............',
					'author'	=> 'jiaru',
					'title'		=> 'my_note',
					'content'	=> 'my_note_content',
					'path'		=> '/4AF64012E9864C/FE23TDE3C',
			)
		the path & content are necessary, and the rest are optional.
	*/
    function updateNote($oauth_access_token, $oauth_access_secret, $noteinfo = array()) {
        $request_url = $this->url_base."/yws/open/note/update.json";

        $params = $this->generateOAuthParams($oauth_access_token);
        $base_string = $this->buildBaseString('POST', $request_url, $params);

	    $oauth_signature_key = urlencode($this->oauth_consumer_secret).'&'.urlencode($oauth_access_secret);
	    $oauth_signature = base64_encode(hash_hmac('sha1', $base_string, $oauth_signature_key, true));

        $response = $this->doSignedMultipartPostRequest($request_url, $params, $oauth_signature, $noteinfo);
        return $response;
    }

    // verified
	/* Delete the specified note.
		$path: path of the note to be deleted.
	*/
    function deleteNote($oauth_access_token, $oauth_access_secret, $path) {
        $request_url = $this->url_base."/yws/open/note/delete.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $request_params['path'] = $path;
        $base_string = $this->buildBaseString('POST', $request_url, $request_params);
        $oauth_signature = $this->sign($base_string, $oauth_access_secret);
        $response = $this->doSignedPostRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }

    // verified
	/* Get the information of the specified note.
		$path: path of the note.
		return: full information of the note in array.
		(example array(	
						"title" : "work journal",
						"author" : "Tom",
						"source" : "http://note.youdao.com",
						"size" : "1024",
						"create_time" : "1323310917"
						"modify_time" : "1323310949"
 						"content" : "<p>This is a test note</p>"
					))
	*/
    function getNote($oauth_access_token, $oauth_access_secret, $path) {
        $request_url = $this->url_base."/yws/open/note/get.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $request_params['path'] = $path;
        $base_string = $this->buildBaseString('POST', $request_url, $request_params);
        $oauth_signature = $this->sign($base_string, $oauth_access_secret);
        $response = $this->doSignedPostRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }
    
    // verified
	/* Move the note to a new notebook.
		$path: full path of the notebook before moving.
		$notebook: target notebook which the note is moving into.
		Both $path and $notebook are necessary.
	*/
    function moveNote($oauth_access_token, $oauth_access_secret, $path, $notebook) {
        $request_url = $this->url_base."/yws/open/note/move.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $request_params['path'] = $path;
        $request_params['notebook'] = $notebook;
        $base_string = $this->buildBaseString('POST', $request_url, $request_params);
        $oauth_signature = $this->sign($base_string, $oauth_access_secret);
        $response = $this->doSignedPostRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }

    // verified
	/* Create a note.
		$noteinfo: the information of the created note in array, like
			array(	'source'	=> 'http://............',
					'author'	=> 'jiaru',
					'title'		=> 'my_note',
					'content'	=> 'my_note_content',
					'notebook'	=> 'my_notebook',
			)
		the content is necessary, and the rest are optional.
	*/
    function createNote($oauth_access_token, $oauth_access_secret, $noteinfo = array()) {
        $request_url = $this->url_base."/yws/open/note/create.json";

        $params = $this->generateOAuthParams($oauth_access_token);
        $base_string = $this->buildBaseString('POST', $request_url, $params);

	    $oauth_signature_key = urlencode($this->oauth_consumer_secret).'&'.urlencode($oauth_access_secret);
	    $oauth_signature = base64_encode(hash_hmac('sha1', $base_string, $oauth_signature_key, true));

	    $request_url = $request_url."?".$normalized_params."&oauth_signature=".$oauth_signature;

        $response = $this->doSignedMultipartPostRequest($request_url, $params, $oauth_signature, $noteinfo);
        return $response;
    }

	// verified
	/* Sign for OAuth.
		$base_string: the base string to be signed.
		$oauth_secret: the oauth secret needed to construct the signature key. This parameter
		is left NULL if do not have oauth secret, this happen while getting the request token
		from the open API server for example.
	*/
	function sign($base_string, $oauth_secret = '') {
		$oauth_signature_key = rawurlencode($this->oauth_consumer_secret).'&';
		if ($oauth_secret != '') {
			$oauth_signature_key .= rawurlencode($oauth_secret);
		}
		$oauth_signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $oauth_signature_key, true)));
		return $oauth_signature;
	}

    // verified
	/* Delete the specified notebook.
		$notebook: the path of the notebook to be deleted.
	*/
    function deleteNotebook($oauth_access_token, $oauth_access_secret, $notebook) {
        $request_url = $this->url_base."/yws/open/notebook/delete.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $request_params['notebook'] = $notebook;
        $base_string = $this->buildBaseString('POST', $request_url, $request_params);
        $oauth_signature = $this->sign($base_string, $oauth_access_secret);
        $response = $this->doSignedPostRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }

    // verified
	/* Create a notebook with name.
		$name: name of the created notebook.
	*/
    function createNotebook($oauth_access_token, $oauth_access_secret, $name) {
        $request_url = $this->url_base."/yws/open/notebook/create.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $request_params['name'] = $name;
        $base_string = $this->buildBaseString('POST', $request_url, $request_params);
        $oauth_signature = $this->sign($base_string, $oauth_access_secret);
        $response = $this->doSignedPostRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }

    // verified
	/* List all the notes under the given notebook.
		$notebook: the path of the notebook.
	*/
    function listNotes($oauth_access_token, $oauth_access_secret, $notebook) {
        $request_url = $this->url_base."/yws/open/notebook/list.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $request_params['notebook'] = $notebook;
        $base_string = $this->buildBaseString('POST', $request_url, $request_params);
        $oauth_signature = $this->sign($base_string, $oauth_access_secret);
        $response = $this->doSignedPostRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }

    // verified
	/* List all the notebooks of the user.
	*/
    function listNotebooks($oauth_access_token, $oauth_access_secret) {
        $request_url = $this->url_base."/yws/open/notebook/all.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $base_string = $this->buildBaseString('POST', $request_url, $request_params);
        $oauth_signature = $this->sign($base_string, $oauth_access_secret);
        $response = $this->doSignedPostRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }

    // verified
	/* Do HTTP POST request, changing the content-type to "multipart/form-data". What's
		more, the OAuth authrization information is putted in the Authorization field
		in the POST header.
		content-type: multipart/form-data
		$post_data: some optional parameters which will be sended along with the
		POST request.
	*/
    function doSignedMultipartPostRequest($request_url, $request_params, $oauth_signature, $post_data = array()) {
        $request_url_full = $request_url;
        if (count($request_params) > 0) {
			$request_params_string = $this->buildParamString($request_params);
			$request_url_full .= '?'.$request_params_string;
		}
		if (isset($oauth_signature)) {
			$request_url_full.='&oauth_signature='.$oauth_signature;
		}
        $params_array_for_header = $this->buildParamsForHeader($request_params, $oauth_signature);
        $params_string_in_header = $this->buildHeaderParamString($params_array_for_header);

        $ch = curl_init($request_url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, TRUE);
	    curl_setopt($ch, CURLOPT_POST, TRUE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);	
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				    'Authorization: '.'OAuth '.$params_string_in_header,
				    'Content-Type: multipart/form-data; boundary='.$boundary,
				    'Content-Length: '.strlen($post_data),
				    ));

	    $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    // verified
	/* Do HTTP POST request with default options.
		content-type: application/x-www-form-urlencoded
		$post_data_array: some optional parameters which will be sended along with the
		POST request.
	*/
    function doSignedPostRequest($request_url, $request_params, $oauth_signature, $post_data_array = array()) {
        $request_url_full = $request_url;
        if (count($request_params) > 0) {
			$request_params_string = $this->buildParamString($request_params);
			$request_url_full .= '?'.$request_params_string;
		}
		if (isset($oauth_signature)) {
			$request_url_full.='&oauth_signature='.$oauth_signature;
		}
        $params_array_for_header = $this->buildParamsForHeader($request_params, $oauth_signature);
        $params_string_in_header = $this->buildHeaderParamString($params_array_for_header);

        $post_data_array = array();
        $post_data = http_build_query($post_data_array);

	    $ch = curl_init($request_url_full);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_POST, TRUE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

	    $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    // verified
	/* Build the OAuth authorization information array with the given parameter array
		and signature. The OAuth parameters will be copied from $params	and the
		$oauth_signature is appended at the end.
		return: the builded parameter array for Authorization filed in http header.
	*/
    function buildParamsForHeader($params = array(), $oauth_signature = "") {
        $params_array_for_header = array(
            'oauth_consumer_key'	=> $params['oauth_consumer_key'],
		    'oauth_nonce'			=> $params['oauth_nonce'],
		    'oauth_signature_method'=> $params['oauth_signature_method'],
		    'oauth_timestamp'		=> $params['oauth_timestamp'],
		    'oauth_token'			=> $params['oauth_token'],
		    'oauth_version'			=> $params['oauth_version'],
		    'oauth_signature'		=> $oauth_signature,
        );
        return $params_array_for_header;
    }

    // verified
	/* Build the OAuth authorization information string which will be included in the
		http header.
		$params: the parameters of OAuth which are needed to be putted in the http header.
		return: the builded string for Authorization field in http header.
	*/
    function buildHeaderParamString($params) {
        $normalized_for_header = array();
        ksort($params);
        foreach ($params as $key => $value) {
            $normalized_for_header[] = rawurlencode($key).'='."\"".rawurlencode($value)."\"";
        }
        $params_in_header = implode(', ', $normalized_for_header);
        return $params_in_header;
    }

    // verified
	/* Get information of the granted user.
		return: user information.
	*/
    function getUserInfo($oauth_access_token, $oauth_access_secret) {
        $request_url = $this->url_base."/yws/open/user/get.json";
        $request_params = $this->generateOAuthParams($oauth_access_token);
        $base_string = $this->buildBaseString('GET', $request_url, $request_params);
		$oauth_signature = $this->sign($base_string, $oauth_access_secret);
		$response = $this->doSignedGetRequest($request_url, $request_params, $oauth_signature);
        return $response;
    }

	// verified
	/* Parse the response to extract the oauth token and secret, and put them
		in the array.
		$response: the response content of the http request to YNote open API server.
		return: an array which contains both the oauth token and secret.
	*/
	function parseTokenResponse($response) {
		$response_split = explode('&', $response);
		$oauth_token_split = explode('=', $response_split[0]);
		$oauth_token = $oauth_token_split[1];
		$oauth_token_secret_split = explode('=', $response_split[1]);
		$oauth_token_secret = $oauth_token_secret_split[1];
		return array(
			'oauth_token'	=> $oauth_token,
			'oauth_token_secret' => $oauth_token_secret,
		);
	}

	// verified
	/* Do HTTP GET request with information for OAuth authorization.
		$request_url: the URL for the request.
		$request_params: parameters of the request in array.
		$oauth_signature: oauth signature for authorization.
		return: http response.
	*/
	function doSignedGetRequest($request_url, $request_params, $oauth_signature) {
		$request_url_full = $request_url;
		if (count($request_params) > 0) {
			$request_params_string = $this->buildParamString($request_params);
			$request_url_full .= '?'.$request_params_string;
		}
		if (isset($oauth_signature)) {
			$request_url_full.='&oauth_signature='.$oauth_signature;
		}
/*if (!isset($_POST['verifier']))
{
	echo $request_url_full;
}
else{
	die($request_url_full);	
}*/
		return HttpClient::quickGet($request_url_full);
	}

	// verified
	/* Build the param string for the http request.
		$params: the http parameters in an array.
		return: the builded param string.
	*/
	function buildParamString($params) {
		$query_param_array = array();
		foreach ($params as $key => $value) {
			$query_param_array[] = rawurlencode($key).'='.rawurlencode($value);
		}
		$query_param_string = implode('&', $query_param_array);
		return $query_param_string;	
	}

	// verified
	/* Generate the default OAuth parameters, including oauth_consumer_key, oauth_nonce,
		oauth_signature_method, oauth_timestamp, oauth_version. The oauth_token and
		oauth_verifier will be added in the returned array if they are given in the parameters.
	*/
	function generateOAuthParams($oauth_token = "", $oauth_verifier = "") {
		$oauth_params = array(
			'oauth_consumer_key'		=> $this->oauth_consumer_key,
			'oauth_nonce'			=> rand(),
			'oauth_signature_method'	=> $this->oauth_signature_method,
			'oauth_timestamp'		=> time(),
			'oauth_version'			=> $this->oauth_version,
//			'oauth_callback'		=> 'http://www.ibiu.org/note2site/php_sdk/index.php',
		);
		if ($oauth_token != "") {
			$oauth_params['oauth_token'] = $oauth_token;
		}
		if ($oauth_verifier != "") {
			$oauth_params['oauth_verifier'] = $oauth_verifier;
		}
		return $oauth_params;
	}

	// verified
	/* Build the base string for signature, the parameters are sorted alphabetically.
		$method: the http request method. (example "GET" or "POST", etc.)
		$request_url: the request url.
		$params: the http parameters of the request.
		return: base string with parameters in sequence.
	*/
	function buildBaseString($method, $request_url, $params = array()) {
		ksort($params);
		$normalized_params = $this -> buildParamString($params);
		$sig = array();
		$sig[] = rawurlencode($method);
		$sig[] = rawurlencode($request_url);
		$sig[] = rawurlencode($normalized_params);

		$base_string = implode('&', $sig);
		return $base_string;
	}

	// verified
	/* Output the information of this YNote client for HTML page.
	*/
	function toString() {
		return "ConsumerKey: ".$this->oauth_consumer_key
			.'<br />'
			."ConsumerSecret: ".$this->oauth_consumer_secret
			.'<br />'
			."RequestToken: ".$this->oauth_request_token
			.'<br />'
			."RequestSecret: ".$this->oauth_request_secret
			.'<br />'
			."AccessToken: ".$this->oauth_access_token
			.'<br />'
			."AccessSecret: ".$this->oauth_access_secret
			.'<br />'
			."Verifier: ".$this->oauth_verifier
			.'<br />'
			."SignatureMethod: ".$this->oauth_signature_method
			.'<br />'
			."OAuthVersion: ".$this->oauth_version;
	}

}

?>
