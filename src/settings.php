<?php
	// Define root path
	defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
	defined('ROOT') ?: define('ROOT', dirname(__DIR__) . DS);
	define('CONFIGPROD', "/home/pali_tchatokey/config/config.ini");
	define('CONFIGDEV', "/Users/palitcha-tokey/config/config.ini");
	
	
	// Load .env file
	if (file_exists(ROOT . '.env')) {
	    $dotenv = new Dotenv\Dotenv(ROOT);
	    $dotenv->load();
	}

	$ini_config = parse_ini_file(CONFIGDEV, true);

	return [
	    'settings' => [
	        'displayErrorDetails' => true,
	        'determineRouteBeforeAppMiddleware' => true,
	        'addContentLengthHeader' => false,
	        // DB Mysql connection
	        'MysqlDB' => [
	            'db.dsn' => $ini_config['mysql']['dsn'],
	            'db.username' => $ini_config['mysql']['username'],
	            'db.password' => $ini_config['mysql']['password'],
	        ],
	        //API Secret
	        'API' => [
	        	'api.secret' => $ini_config['api']['secret']
	        ]
	    ]
	];
