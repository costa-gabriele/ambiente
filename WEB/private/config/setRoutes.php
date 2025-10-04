<?php

use _\Navigation as _NV;

# Setting standard pages routes

_NV\Route::setAdminPage();
_NV\Route::setHomePage();
_NV\Route::setNotFoundPage();

# Common view dir

/*
 * addFolder creates a route for each file
 * in the folder at the moment of the execution,
 * whereas addPattern creates a rule to map a
 * URI pattern to a specified destination
 */

#assert(_NV\Route::addFolder([URI_ROOT . COMMON_VIEW_URI], COMMON_VIEW_DIR));
_NV\Route::addPattern(COMMON_VIEW_URI . '(.+)', realpath(COMMON_VIEW_DIR) . DIR_SEP . '$1');

# Custom pages

_NV\Route::addPage('_/demo');

# Setting web services routes

_NV\Route::addWebService('_/retrieveView');
_NV\Route::addWebService('_/demo/loadFiles');

$fSaved = _NV\Route::save();

echo ($fSaved) ? 'File saved at ' . realpath(ROUTES_CACHE_PATH) : 'Error';
var_dump(_NV\Route::getRoutes());

?>
