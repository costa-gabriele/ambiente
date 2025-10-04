<?php

use _\Network as _NW;
use _\Navigation as _NV;

$retrieveView = new _NW\WebService(_NW\WebService::JSON_REQUEST, _NW\WebService::HTML_RESPONSE);
$requestData = $retrieveView->getRequestData();
$viewName = $requestData['viewName'] ?? null;
$viewValues = $requestData['viewValues'] ?? [];
$valuesRetrieverName = $requestData['valuesRetriever'] ?? null;

if(empty($viewName)) {
	
	$retrieveView->setStatusCode(400);
	
} else {
	
	$viewPath = VIEW_DIR . $viewName . '.' . VIEW_EXTENSION;
	
	if(!file_exists($viewPath)) {
		
		$retrieveView->setStatusCode(404);
		
	} else {
		
		$valuesRetrieverPath = __DIR__ . DIR_SEP . 'viewValuesRetrievers' . DIR_SEP . $valuesRetrieverName . '.php';
		if(!empty($valuesRetrieverName) && file_exists($valuesRetrieverPath)) {
			require $valuesRetrieverPath;
			$valuesRetriever = new $valuesRetrieverName($retrieveView->getRequest());
			$retrievedValues = $valuesRetriever->retrieve();
			$viewValues = !empty($retrievedValues) ? $retrievedValues : $viewValues;
		}

		$retrieveView->setStatusCode(200);
		$view = _NV\View::retrieve($viewName, $viewValues, 1, true);
		$retrieveView->setResponseData($view);
		
	}
	
}

$retrieveView->respond();

?>
