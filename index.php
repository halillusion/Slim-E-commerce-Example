<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use App\Middlewares\AuthMiddleware;
use App\Controllers\AppController;
use App\Controllers\UserController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;

require __DIR__ . '/vendor/autoload.php';

define('KN_ROOT', __DIR__);

$app = AppFactory::create();

/**
 * The routing middleware should be added before the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled
 */
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define Error Handler
$errorHandler = $errorMiddleware->setErrorHandler(\Slim\Exception\HttpNotFoundException::class, function (
    \Psr\Http\Message\ServerRequestInterface $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) {
    $response = new \Slim\Psr7\Response();
    $response->getBody()
    	->write(json_encode(['status' => false, 'message' => 'Adres Bulunamadı.']));

    return $response->withHeader('content-type', 'application/json')->withStatus(404);
});

$app->add(\App\Middlewares\AuthMiddleware::class);
$app->addRoutingMiddleware();

/**
 * Route definitions 
 */
$app->get('/', [AppController::class, 'index']);
$app->get('/prepare-db', [AppController::class, 'prepareDb']);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/login', [UserController::class, 'login']);
    $group->post('/register', [UserController::class, 'register']);
    $group->post('/products', [ProductController::class, 'getProducts']);
    $group->post('/add-to-cart', [OrderController::class, 'addToCart']);
    $group->post('/remove-from-cart', [OrderController::class, 'removeFromCart']);
    $group->post('/cart', [OrderController::class, 'getCart']);
    $group->post('/discounted-cart', [OrderController::class, 'getDiscountedCart']);
    $group->post('/complete-order', [OrderController::class, 'completeOrder']);
    $group->post('/orders', [OrderController::class, 'getOrders']);
    $group->post('/cancel-order', [OrderController::class, 'cancelOrder']);
    /*

    $group->group('/user', function($app) {
	    $app->post('/update-profile', [UserController::class, 'updateProfile']);
	})->add(new Auth());

	$group->group('/products', function($app) {
		$group->post('/update-profile', [UserController::class, 'updateProfile']);
	})->add(new Auth());
	*/
});



$app->run();