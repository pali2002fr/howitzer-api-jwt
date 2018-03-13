<?php
	// Define root path
	defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
	defined('ROOT') ?: define('ROOT', dirname(__DIR__) . DS);
	// Load .env file
	if (file_exists(ROOT . '.env')) {
	    $dotenv = new Dotenv\Dotenv(ROOT);
	    $dotenv->load();
	}

	return [
	    'settings' => [
	        'displayErrorDetails' => true,
	        'determineRouteBeforeAppMiddleware' => true,
	        'addContentLengthHeader' => false,
	        // DB Mysql connection
	        'MysqlDB' => [
	            'db.dsn' => 'mysql:host=localhost;dbname=howitzer',
	            'db.username' => 'root',
	            'db.password' => 'root',
	        ],
	    ],
	];
