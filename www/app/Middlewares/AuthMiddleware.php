<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use \App\Helpers\Base;
use \App\Helpers\Token;
use \App\Models\UserModel;

class AuthMiddleware implements Middleware {

    public function __construct($app) {
    }

    /**
     * Auth Check
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        $route = $request->getUri()->getPath();
        $routeArray = ['/', '/prepare-db', '/api/login', '/api/register', '/api/products'];

        if (in_array($route, $routeArray) === false) { // auth required

            $userId = 0;
            if (isset($request->getHeader('X-Token')[0]) !== false) {

                $token = $request->getHeader('X-Token')[0];
                $userId = Token::verify($token);

                if ($userId) {
                    $userData = (new UserModel)->select('id, email, name')
                        ->where('id', $userId)
                        ->get();
                }
                
            }

            if (isset($userData) !== false) {
                $request = $request->withAttribute('userData', $userData);
            } else {
                $response = new Response();
                $response->getBody()
                    ->write(json_encode(['status' => false, 'message' => 'Oturum blgileri doğru değil!']));

                return $response->withHeader('content-type', 'application/json');
            }
        }

        return $handler->handle($request);
    }
}