<?php
/**
 * Define database credentials
 */
// define("DB_HOST", "localhost"); 
// define("DB_NAME", "nextpost"); 
// define("DB_USER", "root"); 
// define("DB_PASS", ""); 
// define("DB_ENCODING", "utf8"); // DB connnection charset

$config = require __DIR__ . '/../../../LocalConfigDB.php'; // điều chỉnh đường dẫn nếu khác

/**
 * Define database credentials
 */
define("DB_HOST", $config['host']); 
define("DB_NAME", $config['database_frontend']); 
define("DB_USER", $config['username']); 
define("DB_PASS", $config['password']); 
define("DB_ENCODING", $config['charset']);
/**
 * Define DB tables
 */
define("TABLE_PREFIX", "np_");

// Set table names without prefix
define("TABLE_USERS", "users");
define("TABLE_ACCOUNTS", "accounts");
define("TABLE_PACKAGES", "packages");
define("TABLE_POSTS", "posts");
define("TABLE_GENERAL_DATA", "general_data");
define("TABLE_OPTIONS", "options");
define("TABLE_ORDERS", "orders");

define("TABLE_FILES", "files");
define("TABLE_CAPTIONS", "captions");
define("TABLE_PROXIES", "proxies");

define("TABLE_PLUGINS", "plugins");
define("TABLE_THEMES", "themes");
