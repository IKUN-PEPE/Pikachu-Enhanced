<?php

declare(strict_types=1);

namespace Pikachu\Tests\Unit;

use Pikachu\Http\Request;
use Pikachu\Http\Response;
use Pikachu\Http\Router;

class RouterTest
{
    public function testRouteMatching(): bool
    {
        $router = new Router();
        $router->get('/api/health', function (Request $req) {
            return Response::json(['status' => 'healthy']);
        });

        $req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/health']);
        $res = $router->dispatch($req);

        assert($res->getStatusCode() === 200, 'Healthcheck route status must be 200');
        assert(str_contains($res->getContent(), 'healthy'), 'Response body missing healthy status');
        return true;
    }

    public function testNotFoundRoute(): bool
    {
        $router = new Router();
        $req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/non/existent']);
        $res = $router->dispatch($req);

        assert($res->getStatusCode() === 404, 'Non-existent route must return 404');
        return true;
    }
}
