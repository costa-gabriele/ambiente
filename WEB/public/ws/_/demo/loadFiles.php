<?php

use _\Network as _NW;

$loadFiles = new _NW\WebService(_NW\WebService::MULTIPART_REQUEST, _NW\WebService::JSON_RESPONSE);

$requestData = $loadFiles->getRequestData();
$requestFiles = $loadFiles->getRequestFiles();

$loadFiles->saveAllFiles();
$loadFiles->setStatusCode(200);
$loadFiles->setResponseData(['requestData' => $requestData, 'requestFiles' => $requestFiles]);

$loadFiles->respond();

?>
