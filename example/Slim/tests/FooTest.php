<?php
use Slim\Http\Environment;
use Slim\Http\Request;
use Eloqunit\Example\Slim\App;
use Eloqunit\Constraint;
use Eloqunit\Eloqunit;
use Illuminate\Database\Capsule\Manager;

class FooTest extends Eloqunit
{
    /* $var Slim\App $app */
    protected static $app;
    
    public function getDatabase(): Manager
    {
        return static::$app->getContainer()['eloquent'];
    }

    public function setup(): void
    {
        if (!static::$app) {
            static::$app = App::create();
        }
        parent::setup();
        $this->seed('foo', [
            ['id' => 1, 'value' => 'foo'],
            ['id' => 2, 'value' => 'bar'],
        ]);
    }

    public function testListFoo()
    {
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/foo',
        ]);
        $request = Request::createFromEnvironment($environment);
        static::$app->getContainer()['request'] = $request;
        $response = static::$app->run(true);
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody());
        $this->assertCount(2, $result);
    }

    public function testGetFoo()
    {
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/foo/1',
        ]);
        $request = Request::createFromEnvironment($environment);
        static::$app->getContainer()['request'] = $request;
        $response = static::$app->run(true);
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody());
        $this->assertEquals(1, $result->id);
        $this->assertEquals('foo', $result->value);
    }
    
    public function testPostFoo()
    {
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/foo',
        ]);
        $body = new \Slim\Http\RequestBody();
        $body->write(json_encode(['id' => 3, 'value' => 'bat']));
        $body->rewind();
        $request = Request::createFromEnvironment($environment)
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');
        static::$app->getContainer()['request'] = $request;
        $response = static::$app->run(true);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertRowExists('foo', ['id' => 3]);
        $this->assertRowMatches('foo', ['id' => 3], ['value' => 'bat', 'created_at' => Constraint::IsNotNull(), 'updated_at' => Constraint::IsNull()]);
    }

    public function testDeleteFoo()
    {
        $this->assertRowExists('foo', ['id' => 2]);
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
            'REQUEST_URI' => '/foo/2',
        ]);
        $request = Request::createFromEnvironment($environment);
        static::$app->getContainer()['request'] = $request;
        $response = static::$app->run(true);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertRowNotExists('foo', ['id' => 2]);
    }

    public function testUpdateFoo()
    {
        $this->assertRowExists('foo', ['id' => 2]);
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'PATCH',
            'REQUEST_URI' => '/foo/2',
        ]);
        $body = new \Slim\Http\RequestBody();
        $body->write(json_encode(['id' => 2, 'value' => 'new.value']));
        $body->rewind();
        $request = Request::createFromEnvironment($environment)
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');
        static::$app->getContainer()['request'] = $request;
        $response = static::$app->run(true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRowMatches('foo', ['id' => 2], ['value' => 'new.value']);
    }
}
