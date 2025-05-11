<?php

use _\Navigation as _NV;

# Setting standard pages routes

assert(_NV\Route::setAdminPage());
assert(_NV\Route::setHomePage());
assert(_NV\Route::setNotFoundPage());

# Common view dir

/*
 * addFolder creates a route for each file
 * in the folder at the moment of the execution,
 * whereas addPattern creates a rule to map a
 * URI pattern to a specified destination
 */

#assert(_NV\Route::addFolder([URI_ROOT . COMMON_VIEW_URI], COMMON_VIEW_DIR));
assert(_NV\Route::addPattern(COMMON_VIEW_URI . '(.+)', realpath(COMMON_VIEW_DIR) . DIR_SEP . '$1'));

# Custom pages

assert(_NV\Route::addPage('_/demo'));

# Setting web services routes

assert(_NV\Route::addWebService('_/retrieveView'));
assert(_NV\Route::addWebService('_/demo/loadFiles'));

$fSaved = _NV\Route::save();

echo ($fSaved) ? 'File saved at ' . realpath(ROUTES_CACHE_PATH) : 'Error';
var_dump(_NV\Route::getRoutes());

?>
