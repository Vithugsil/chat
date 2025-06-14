<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use App\Controller\AuthController;
use App\Controller\UserController;
use App\Controller\HealthController;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->post('/token', [AuthController::class, 'login']);
$app->get('/token', [AuthController::class, 'validate']);

$app->post('/user', [UserController::class, 'create']);
$app->get('/user', [UserController::class, 'getByEmail']);
$app->put('/user/{id}', [UserController::class, 'update']);
$app->delete('/user/{id}', [UserController::class, 'delete']);

$app->get('/health', [HealthController::class, 'check']); 

$app->get('/allUsers', function ($req, $res, $args) {
    $svc = new \App\Service\AuthService();
    $users = $svc->getAllUsers();
    $res->getBody()->write(json_encode($users));
    return $res->withHeader('Content-Type', 'application/json');
});

$app->run();
