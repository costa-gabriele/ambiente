<?php

use _\Network as _NW;
use _\Navigation as _NV;

$retrieveView = new _NW\WebService(_NW\WebService::JSON_REQUEST, _NW\WebService::HTML_RESPONSE);
$requestData = $retrieveView->getRequestData();
$viewName = $requestData['viewName'] ?? null;
$viewValues = $requestData['viewValues'] ?? [];

if(empty($viewName)) {
	
	$retrieveView->setStatusCode(400);
	
} else {
	
	$viewPath = VIEW_DIR . $viewName . '.' . VIEW_EXTENSION;
	
	if(!file_exists($viewPath)) {
		
		$retrieveView->setStatusCode(404);
		
	} else {
		
		$retrieveView->setStatusCode(200);
		$view = _NV\View::retrieve($viewName, $viewValues, true);
		$retrieveView->setResponseData($view);
		
	}
	
}

$retrieveView->respond();

?>
