<?php

use _\Navigation as _NV;

$routes = _NV\Route::getRoutes();
$exactRoutes = $routes['exactURIs'];
$patternRoutes = $routes['patterns'];
_NV\View::retrieve(BASE_DIR_NAME . '/admin/main', ['exactRoutes' => $exactRoutes, 'patternRoutes' => $patternRoutes]);

?>
