<?php
	// DIC configuration

	$container = $app->getContainer();

	// DB connection: Mysql
	$container['pdo'] = function ($c) {
	    $settings = $c->get('settings')['MysqlDB'];
	    return new App\Library\Database\PdoAdapter(
	    	$settings['db.dsn'], 
	    	$settings['db.username'], 
	    	$settings['db.password']
	    );
	};

	$container['UserMapper'] = function ($container) {
	    return new App\Model\Mapper\UserMapper($container['pdo']);
	};

	$container['HowitzerMapper'] = function ($container) {
	    return new App\Model\Mapper\HowitzerMapper($container['pdo']);
	};

	$container['AngleMapper'] = function ($container) {
	    return new App\Model\Mapper\AngleMapper($container['pdo']);
	};

	$container['DistanceMapper'] = function ($container) {
	    return new App\Model\Mapper\DistanceMapper($container['pdo']);
	};

	$container['ShotMapper'] = function ($container) {
	    return new App\Model\Mapper\ShotMapper(
	    	$container['pdo'],
	    	$container['UserMapper'],
	    	$container['HowitzerMapper'],
	    	$container['TargetMapper'],
	    	$container['DistanceMapper'],
	    	$container['SpeedMapper'],
	    	$container['AngleMapper']
	    );
	};

	$container['SpeedMapper'] = function ($container) {
	    return new App\Model\Mapper\SpeedMapper($container['pdo']);
	};

	$container['TargetMapper'] = function ($container) {
	    return new App\Model\Mapper\TargetMapper($container['pdo']);
	};

	$container['ResultMapper'] = function ($container) {
	    return new App\Model\Mapper\ResultMapper(
	    	$container['pdo'],
	    	$container['UserMapper'],
	    	$container['ShotMapper']
	    );
	};

	$container['TokenMapper'] = function ($container) {
	    return new App\Model\Mapper\TokenMapper(
	    	$container['pdo'],
	    	$container['UserMapper']
	    );
	};

	$container['ShotService'] = function ($container) {
	    return new App\Service\ShotService($container['ShotMapper']);
	};

	$container['UserService'] = function ($container) {
	    return new App\Service\UserService($container['UserMapper']);
	};
