<?php

use _\Navigation as _NV;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'facade.php';

$requestURI = $_SERVER['REQUEST_URI'];

# Routes initialization
if(!file_exists(ROUTES_CACHE_PATH)) {require ROUTES_INIT_PATH;}

$destination = _NV\Route::resolve($requestURI);
$destinationInfo = $destination['info'];

if(!empty($destinationInfo['path']) && file_exists($destinationInfo['path'])) {
	
	$fileExtension = pathinfo($destinationInfo['path'], PATHINFO_EXTENSION);
	
	switch($fileExtension) {
		case 'css':
			header('Content-type: text/css');
			break;
		case 'js':
			header('Content-type: text/javascript');
			break;
		default:
			null;
	}
	
	$URI_DATA = $destination['input'];

	if($destinationInfo['type'] == _NV\Route::PAGE_TYPE && $fileExtension == PAGE_EXTENSION) {
		require CONFIG_DIR . 'custom.php';
	}

    require $destinationInfo['path'];
	
} else {

    header('HTTP/1.1 404 Not found');
	require _NV\Route::getNotFound();

}

?>