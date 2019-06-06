<?php
define('ROOT_DIR', dirname(__FILE__));
define('DAO_DIR', ROOT_DIR.'/dao');
define('MODEL_DIR', ROOT_DIR.'/model');
define('VIEW_DIR', ROOT_DIR.'/view');
define('CONTROL_DIR', ROOT_DIR.'/control');
require_once(ROOT_DIR.'/config.inc.php');


$route_params = formatRouteUrl();

if (isset($route_params['model'])) {
	$controler = $route_params['model'];
}
else {
	$controler = 'error';
}

if (!file_exists(CONTROL_DIR.'/'.$controler.'.php')) {
	$controler = '404';
}

require_once(CONTROL_DIR.'/'.$controler.'.php');

function formatRouteUrl()
{
	$request_url = $_SERVER['REQUEST_URI'];
	$tmp_array = explode('?', $request_url);
	$request_url = $tmp_array[0];
	$tmp_array = explode('/',$request_url);
	for ($i = count($tmp_array)-1; $i >=0 ; $i--) { 
		if (empty($tmp_array[$i]))
		{
			unset($tmp_array[$i]);
		}
	}
	$tmp_array = array_values($tmp_array);
	$count = count($tmp_array);

	$route_params = array();
	if ($count > 0) {
		$route_params['model'] = $tmp_array[0];
	}

	if ($count > 1) {
		$route_params['params'] = array_slice($tmp_array,1);
	}
	
	return $route_params;
}
?>