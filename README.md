# ambiente

A PHP framework for web development (work in progress).

This projects is intended to collect some useful functionalities for web development (mostly PHP and Javascript).  
In particular, it simplifies route management and the dynamic rendering of HTML pages.

## Directory structure

#### DB
- _install.txt_
#### SH
- `bin`
   - `_`
      - _.install.sh_
      - _deploy.sh_
      - _deployParams.sh_
- `etc`
  - `_`
    - _installIgnore.txt_
#### WEB
- `private`
  - `admin`
    - _initRoutes.php_
    - _router.php_
  - `classes`
    - `_`
      - `Auth`
      - `Database`
      - `Files`
      - `Navigation`
      - `Network`
      - `Utilities`
  - `config`
    - _autoloader.php_
    - _custom.php_
    - _params.php_
    - _polyfill.php_
  - _facade.php_
- `public`
  - `pages`
  - `views`
    - `_`
      - `_common`
        - `modules`
        - `style`
  - `ws`
- _.htaccess_

## Installation, deployment, and configuration
The shell script `SH/bin/_/.install.sh` installs the framework in the directory provided as the first argument. The second argument is either `i` for an initial, complete installation, or `u`, to update an existing installation. (You can also just manually copy the files inside your project directory). The file of the installation script is hidden because it is not intended to be used in the project folder in which the framework is installed.

The shell script `SH/bin/_/deploy.sh` executes the deployment of the project. The first argument is either `web`, for a deployment of the `WEB` directory, or `db`, to execute the SQL files in `DB/install.txt`. The configuration parameters for the deployment can be set in the file `SH/bin/_/deployParams.sh`.

The configuration of the server side web environment is managed with the file `WEB/private/config/params.php`. If your project is in a subdirectory of the web server (as in the case of a local development environment), the project root must be specified in the constant `URI_ROOT` (e.g. `URI_ROOT = '/myProject/'`).

The Javascript configuration file is `WEB/public/views/_/_common/modules/_/config.js`. Here you should set up the base URL of the web site.

## Dynamic HTML
In the `WEB/public` directory, there are two subdirectories intended for the web pages files: `pages` and `views`.  
The .php files that contain the back-end elaboration should go in the `pages` directory, whereas `views` is for the front-end files (e.g. .html, .css, .js).  
Each .php file in `pages` must have a corresponding subdirectory in `views`. For example, the front-end files for the page `pages/category1/page1.php` should go in the directory `public/views/category1/page1/`.  

The HTML file for the page can be retrieved by the .php file using the static method `retrieve(viewName, viewData)` of the class `\_\Navigation\View` (in the directory `private/classes/_/Navigation`), where `viewName` is the path of the .html file relative to the `views` directory, without the extension (e.g. `category1/page1/main`) and `viewData` is an array containing the dynamic content of the page. The dynamic parts of the page can be handled with placeholders and tags that are parsed by the View class.

The Javascript module `public/views/_/_common/modules/_/req.js` provides a function to retrieve a view through an asynchronous call:  
`retrieveView(viewName, serverElabData, clientElabData)`  
The second argument is an object containing the values to be substituted server side (PHP), whereas the values provided in the third argument are processed client side (Javascript).

### Placeholders
A placeholder for a dynamic value, to be substituted with the content received from the .php file, is enclosed by double curly braces (e.g. `{{text.title}}`); the `viewData` array passed as the second argument to the `View::retrieve` method should have a corresponding entry, i.e. `['text' => ['title' => 'The title of the page']]`.

### Tags
More complex operations can be performed on the view with tags. Tags are written inside HTML comments, and they too are enclosed  by double curly braces. Hence, the general syntax for a tag is: `<!--{{TAG(arg)}}-->`. The argument is not always present, and if a tag needs to be opened and closed, the opening tag has a colon before the closing braces and the closing one has a semicolon instead.

The available tags are:

`<!--{{VIEW(viewName)}}-->`  
Includes a sub-view inside the current view.

`<!--{{VIEW:}}-->` / `<!--{{VIEW;}}-->`  
This tag is used to frame the part of the HTML that is relevant for the view, i.e. the part that has to be retrieved when the view is required. For example, if a view should contain only a part of a web page (e.g. a section, a table), because it is intended to be included by another view, it may still be useful, in order to preview it autonomously, to add all the elements required for a valid HTML document (e.g. the html and head tags, a reference to a style sheet); these parts of the document should not, however, be considered when the view is included by another view.

`<!--{{ABS:}}-->` / `<!--{{ABS;}}-->`  
This tag encloses an HTML tag that has a `href` or `src` attribute, and transforms the content of that attribute, that is supposed to be a relative path, in the absolute path obtained considering as base url the path of the view directory of the file.  

`<!--{{FOREACH(content):}}-->` / `<!--{{FOREACH(content);}}-->`  
Repeats the HTML that it encloses for each element of the array indicated by the argument.  
Inside the loop, the placeholder `{{[#]}}` stands for the current key of the array. If the array contains simple values, the current value is indicated by `{{[@]}}`; if the value is itself an array, the value is indicated by the name of the key instead of the at sign.

### Note

A demo of these functionalities can be found in the page `_/demo`.

Notice that the syntax of both placeholders and tags is intended to maintain a valid HTML document. An .html file that contains placholders and tags can be opened with a web browser to see a preview of the page; the tags are ignored since they are inside comments, and placeholders will be showed literally.

## Routes

The .htaccess file redirects every request to the router file `private/admin/router.php` (this is the default behavior, but the web server configuration file can be edited to redirect only certain kinds of requests).

The routes are managed by the the class `_\Navigation\Route` in the directory `private/classes/_/Navigation`. The main methods to set up a route are:

- `add(requestURIs, destinationInfo)`  
Creates a route for all the URIs contained in the array passed as the first argument, mapping them to the destination specified with the second argument, which is an array that can have these keys:  
  - _type_: one of the following values: `Route::FILE_TYPE`, `Route::PAGE_TYPE`, `Route::WS_TYPE`;
  - _path_: the path of the file that has to be mapped to the request URI(s);
  - _label_: can be used to group routes (e.g. all the routes referring to the same page can have the same label).

  The wrapper methods presented below all use this basic method, but are easier to use, because they manage all the operations needed for setting up standard routes. However, in certain cases it may be necessary to add a single route with this basic method.

- `addPage(pageName, requestURIs)`  
Creates a route for a page. The name of the page, to be passed as the first argument, is the path relative to `public/pages/`, without any file extension. The second argument is optional, and it's an array of the URIs that are to be mapped to the page; if it's not provided, the default URI is used, which is the page name appended to the URI root for page requests, defined by the global constant PAGE_URI_ROOT in `private/config/params`. The default root is the base URL of the website. So for example, to set up a route for the page `www.site.org/about/`, the command would be `Route::addPage('about')`. This maps the URI to the file `public/pages/about.php` and also sets up routes for the files in `public/views/about`. Notice that routes will be created only for the files that are in the view folder at the moment of the execution of the command, and if a file is later added, its route has to be added with `Route::add()`;

- `addWebService(webServiceName, requestURIs)`  
Creates a route for a web service. The name of the web service, to be passed as the first argument, is the path relative to `public/ws/`, without any file extension. The second argument is optional, and it's an array of the URIs that are to be mapped to the page; if it's not provided, the default URI is used, which is the web service name appended to the URI root for web service requests, defined by the global constant WS_URI_ROOT in `private/config/params`. The default root is obtained appending `ws` to the base URL of the website. So for example, to set up a route for the web service `www.site.org/web-service`, the command would be `Route::addWebService('web-service')`. This maps the URI to the file `public/ws/web-service.php`.

- `addPattern(requestURIPattern, destinationPath)`  
Creates a rule that maps all the requests that match the pattern provided as the first argument, to the path provided as the second argument. Regular expressions group captures can be used in defining these rules: for example, `Route::addPattern('ruleDir/' . '(.+)', 'ruleDir/route/' . '$1')` would map `www.site.org/ruleDir/test` to  `www.site.org/ruleDir/route/test`.

- `save()`  
Serializes the routes array and saves it in the file `private/admin/routes.ser` (a different file path can be specified in the constant ROUTES_CACHE_PATH in `private/config/params.php`)

- `getRoutes()`  
Returns the array containing all the existing routes.

A few standard routes are initially set, using the methods discussed above, in `private/admin/initRoutes.php`.  
The page `_/admin` shows all the existing routes.

## Other functionalities

### Web services
The class `_\Network\WebService` provides useful methods to create a web service controller. See for example the demo webservice `public/ws/_/demo/loadFiles.php`.
