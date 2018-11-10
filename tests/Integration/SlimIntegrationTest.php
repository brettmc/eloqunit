<?php
namespace Eloqunit\Test\Integration;

use Eloqunit\Eloqunit;
use Eloqunit\Example\Slim\App;
use Illuminate\Database\Capsule\Manager;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;

class SlimIntegrationTest extends Eloqunit
{
    private static $app;

    public function getDatabase(): Manager
    {
        return self::$app->getContainer()->get('eloquent');
    }

    public function setup()
    {
        if (!self::$app) {
            self::$app = App::create();
        }
        parent::setup();
        $this->seed('foo', [
            ['id' => 1, 'value' => 'foo.one'],
            ['id' => 2, 'value' => 'foo.two'],
        ]);
    }

    private function dispatch(string $uri, string $method = 'GET', array $data = null): Response
    {
        $environment = Environment::mock([
            'REQUEST_URI' => $uri,
            'REQUEST_METHOD' => $method,
        ]);
        $request = Request::createFromEnvironment($environment);
        if ($data) {
            $body = new RequestBody();
            $body->write(json_encode($data));
            $body->rewind();
            $request = $request->withBody($body)
                ->withAddedHeader('Content-Type', 'application/json');
        }
        self::$app->getContainer()['request'] = $request;
        return self::$app->run(true);
    }

    public function testList()
    {
        $response = $this->dispatch('/foo');
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody());
        $this->assertCount(2, $result);
    }

    public function testPost()
    {
        $data = [
            'id' => 'id.new',
            'value' => 'value.new',
        ];
        $response = $this->dispatch('/foo', 'POST', $data);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertRowCount(3, 'foo');
        $this->assertRowMatches('foo', ['id' => 'id.new'], ['value' => 'value.new']);
    }

    public function testGet()
    {
        $response = $this->dispatch('/foo/1');
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody());
        $this->assertEquals('1', $result->id);
        $this->assertEquals('foo.one', $result->value);
    }

    public function testPatchUpdatesRowAndSetsUpdatedAt()
    {
        $data = [
            'id' => '1',
            'value' => 'value.new',
        ];
        $response = $this->dispatch('/foo/1', 'PATCH', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRowCount(2, 'foo');
        $this->assertRowMatches('foo', ['id' => '1'], ['value' => 'value.new', 'updated_at' => \Eloqunit\Constraint::IsNotNull()]);
    }

    public function testDelete()
    {
        $response = $this->dispatch('/foo/1', 'DELETE');
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertRowNotExists('foo', ['id' => '1']);
    }
}
