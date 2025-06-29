<?php

# General

const DIR_SEP =  DIRECTORY_SEPARATOR;

# Files and directories naming conventions

const BASE_DIR_NAME = '_';
const PAGE_EXTENSION = 'php';
const WS_EXTENSION = 'php';
const VIEW_EXTENSION = 'html';

# URIs

const URI_ROOT = '/';
const PAGE_URI_ROOT = URI_ROOT;
const WS_URI_ROOT = URI_ROOT . 'api/v1/';
const COMMON_VIEW_URI = BASE_DIR_NAME . '/_common/';
const ADMIN_PAGE_URI = BASE_DIR_NAME . '/admin/';
const HOME_PAGE_URI = 'home/';
const NOT_FOUND_PAGE_URI = '404/';
const ADMIN_PAGE_NAME = BASE_DIR_NAME . '/admin';
const HOME_PAGE_NAME = 'home';
const NOT_FOUND_PAGE_NAME = '404';

# Private directories and files

const CONFIG_DIR = __DIR__ . DIR_SEP;
const PRIVATE_DIR = CONFIG_DIR . '..' . DIR_SEP;
const ADMIN_DIR = PRIVATE_DIR . 'admin' . DIR_SEP;
const CLASSES_DIR = PRIVATE_DIR . 'classes' . DIR_SEP;
const LOG_DIR = PRIVATE_DIR . 'log' . DIR_SEP;
const UPLOADED_FILES_DIR = PRIVATE_DIR . 'files' . DIR_SEP;
const ROUTES_CACHE_PATH = ADMIN_DIR . 'routes.ser';
const ROUTES_INIT_PATH = ADMIN_DIR . 'initRoutes.php';

# Public directories

const PUBLIC_DIR = PRIVATE_DIR . '..' . DIR_SEP . 'public' . DIR_SEP;
const VIEW_DIR = PUBLIC_DIR . 'views' . DIR_SEP;
const PAGE_DIR = PUBLIC_DIR . 'pages' . DIR_SEP;
const WS_DIR = PUBLIC_DIR . 'ws' . DIR_SEP;
const COMMON_VIEW_DIR = VIEW_DIR . BASE_DIR_NAME . DIR_SEP . '_common' . DIR_SEP;

# Session management preferences

const SESSION_ARRAY_KEY = 'session';
const DEFAULT_SESSION_NAME = 'session';
const DEFAULT_SESSION_LIFETIME = 60 * 60 * 12;

# DB connection

const DBMS = 'MYSQL';
const DB_HOST = 'localhost';
const DB_SCHEMA = 'schema';
const DB_USER = 'root';
const DB_PASSWORD = '';
const DB_ENCODING = 'utf8mb4';

?>
