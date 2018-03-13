<?php
	// Define root path
	defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
	defined('ROOT') ?: define('ROOT', dirname(__DIR__) . DS);
	defined('MYSQLCONF', "/home/pali_tchatokey/config/mysql_config.ini")
	// Load .env file
	if (file_exists(ROOT . '.env')) {
	    $dotenv = new Dotenv\Dotenv(ROOT);
	    $dotenv->load();
	}

	$ini_mysql = parse_ini_file(MYSQLCONF, true);

	return [
	    'settings' => [
	        'displayErrorDetails' => true,
	        'determineRouteBeforeAppMiddleware' => true,
	        'addContentLengthHeader' => false,
	        // DB Mysql connection
	        'MysqlDB' => [
	            'db.dsn' => $ini_mysql['mysql']['dsn'],
	            'db.username' => $ini_mysql['mysql']['username'],
	            'db.password' => $ini_mysql['mysql']['password'],
	        ],
	    ],
	];
