<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/bar', function() {
    $this->get('', function (Request $request, Response $response, array $args) {
        $eloquent = $this->get('eloquent');
        $x = $eloquent->table('foo')->get();

        return $response->withJson($x);
    });
    $this->post('', function (Request $request, Response $response, array $args) {
        $eloquent = $this->get('eloquent');
        $body = $request->getParsedBody();
        $eloquent->table('foo')->insert((array)$body);
        return $response->withStatus(201);
    });
    
    $this->group('/{id}', function() {
        $this->patch('', function(Request $request, Response $response, array $args) {
            $eloquent = $this->get('eloquent');
            $body = $request->getParsedBody();
            $eloquent->table('foo')->where('id', $args['id'])->update((array)$body + ['updated_at' => time()]);
            return $response->withStatus(200);
        });
        $this->delete('', function(Request $request, Response $response, array $args) {
            $eloquent = $this->get('eloquent');
            $eloquent->table('foo')->delete(['id' => $args['id']]);
            return $response->withStatus(204);
        });
        $this->get('', function(Request $request, Response $response, array $args) {
            $eloquent = $this->get('eloquent');
            $x = $eloquent->table('foo')->where('id', $args['id'])->limit(1)->get()->first();
            if (!$x) {
                throw new Slim\Exception\NotFoundException();
            }
            return $response->withJson($x);
        });
    });
});
