<?php

namespace App;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class App
{
    /**
     * Stores an instance of the Slim application.
     *
     * @var \Slim\App
     */
    private $app;

    public function __construct() {

        $settings = require __DIR__ . '/../src/settings.php';

        $container = new \Slim\Container($settings);

        $app = new \Slim\App($container);

        // Set up dependencies
        require __DIR__ . '/../src/dependencies.php';

        // Register middleware
        require __DIR__ . '/../src/middleware.php';

        // Register routes
        require __DIR__ . '/../src/routes.php';

        $this->app = $app;
        //$this->setUpDatabaseManager();
    }

    /**
     * Get an instance of the application.
     *
     * @return \Slim\App
     */
    public function get()
    {
        return $this->app;
    }
}