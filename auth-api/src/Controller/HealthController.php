<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\UserModel;
use App\Utils\RedisCache;

class HealthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function check(Request $request, Response $response, array $args): Response
    {
        $status = [
            'app' => 'ok',
            'mysql' => 'ok',
            'redis' => 'ok'
        ];

        $httpStatus = 200;

        // Check MySQL connection
        try {
            // Try to execute a simple query
            $this->userModel->getAllUsers();
        } catch (\Exception $e) {
            $status['mysql'] = 'error: ' . $e->getMessage();
            $httpStatus = 503;
        }

        // Check Redis connection
        try {
            $redis = RedisCache::getClient();
            $redis->ping();
        } catch (\Exception $e) {
            $status['redis'] = 'error: ' . $e->getMessage();
            $httpStatus = 503;
        }

        $payload = json_encode($status);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($httpStatus);
    }
}
