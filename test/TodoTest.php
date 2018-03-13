<?php
use App\App;
use Slim\Http\Environment;
use Slim\Http\Request;
class TodoTest extends PHPUnit_Framework_TestCase
{
    protected $app;
    public function setUp()
    {
        $this->app = (new App())->get();
    }
    public function testTodoGetAll() {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/users',
          ]);

        $req = Request::createFromEnvironment($env);
        var_dump($this->app); die;
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        //$this->assertSame($response->getStatusCode(), 200);
        var_dump('pali' . $response->getBody()); die;
        $result = json_decode($response->getBody(), true);
        $this->assertSame($result["message"], "Hello, Todo");
    }

    private function setUpDatabaseManager()
    {
        // Register the database connection with Eloquent
        $capsule = $this->app->getContainer()->get('capsule');
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}