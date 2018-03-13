<?php
namespace App;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class App
{
	private $app;

	public function __construct() {

        $settings = require __DIR__ . '/../app/settings.php';

		$container = new \Slim\Container($settings);

		$app = new \Slim\App($container);

		// Set up dependencies
		require __DIR__ . '/../app/dependencies.php';

		// Register middleware
		require __DIR__ . '/../app/middleware.php';

		// Register routes
		require __DIR__ . '/../app/routes.php';

	 	$this->app = $app;
    }

    public function get()
    {
        return $this->app;
    }
}