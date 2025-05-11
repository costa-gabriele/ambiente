# ambiente

A PHP framework for web development (work in progress).

This projects is intended to collect some useful functionalities for web development (mostly PHP and Javascript).  
In particular, it simplifies route management and the dynamic rendering of HTML pages.

Here's a schema of the directory structure:

#### DB
- install.txt

#### SH

#### WEB
- `private`
  - `admin`
  - `classes`
    - `_`
  - `config`
  - facade.php
- `public`
  - `pages`
  - `views`
  - `ws`
- .htaccess

## Dynamic HTML
In the `WEB/public` directory, there are two subdirectories intended for the web pages files: `pages` and `views`.  
The .php files that contain the back-end elaboration should go in the `pages` directory, whereas `views` is for the front-end file (e.g. .html, .css, .js).  
Each .php file in `pages` must have a corresponding subdirectory in `views`. For example, the front-end files for the page pages/category1/page1.php should go in the directory `views/category1/page1/`.  

The HTML file for the page can be retrieved by the .php file using the static method `retrieve(viewName, viewData)` of the class `\_\Navigation\View` (in the directory `private/classes/_/Navigation`), where `viewName` is the path of the .html file relative to the `views` directory, without the extension (e.g. category1/page1/main) and `viewData` is an array containing the dynamic content of the page. The dynamic parts of the page can be handled with placeholders and tags that are parsed by the View class.  

### Placeholders
A placeholder for a dynamic value, to be substituted with the content received from the .php file, is enclosed by double curly braces (e.g. `{{text.title}}`); the `viewData` array passed as the second argument to the `View::retrieve` method should have a corresponding entry, i.e. `['text' => ['title' => 'The title of the page']]`.

### Tags
More complex operations can be performed on the view with tags. Tags are written inside HTML comments, and they too are enclosed  by double curly braces. Hence, the general syntax for a teg is: `<!--{{TAG(arg)}}-->`. The argument is not always present, and if a tag needs to be opened and closed, the opening tag has a colon before the closing braces and the closing one has a semicolon instead.

The available tags are:

`<!--{{VIEW(viewName)}}-->`  
Includes a sub-view inside the current view

`<!--{{VIEW:}}-->` / `<!--{{VIEW;}}-->`  
This tag is used to frame the part of the HTML that is relevant for the view, i.e. the part that has to be retrieved when the view is required. For example, if a view should contain only a part of a web page (e.g. a section, a table), because it is intended to be included by another view, it may still be useful, in order to preview it autonomously, to add all the elements required for a valid HTML document (e.g. the html and head tags, a reference to a style sheet); these parts of the document should not, however, be considered when the view is included by another view.

`<!--{{ABS:}}-->` / `<!--{{ABS;}}-->`  
This tag encloses an HTML tag that has a `href` or `src` attribute, and transforms the content of that attribute, that is supposed to be a relative path, in the absolute path obtained considering as base url the path of the view directory of the file.  

`<!--{{FOREACH(content):}}-->` / `<!--{{FOREACH(content);}}-->`  
Repeats the HTML that it encloses for each element of the array indicated by the argument.  
Inside the loop, the placeholder `{{[#]}}` stands for the current key of the array. If the array contains simple values, the current value is indicated by `{{[@]}}`; if the value is itself an array, the value is indicated by the name of the key insted of the at sign.

### Note

A demo of these functionalities can be found in the page `/_/demo`.

Notice that the syntax of both placeholders and tags is intended to maintain a valid HTML document. An .html file that contains placholders and tags can be opened with a web browser to see a previerw of the page; the tags are ignored since they are inside comments, and placeholders will be showed literally.

## Routes

## Other functionalities
