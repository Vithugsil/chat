<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\UserModel;
use App\Utils\RedisCache;
use App\Utils\DatabaseUtils;

class HealthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    //mensagem do check
    public function check(Request $request, Response $response, array $args): Response
    {
        $status = [
            'app' => 'ok',
            'mysql' => 'ok',
            'redis' => 'ok'
        ];

        $httpStatus = 200;

        // verifica o bd com o PDO do php 
        try {
            DatabaseUtils::checkConnection($this->userModel->getPdo());
        } catch (\Exception $e) {
            $status['mysql'] = 'error: ' . $e->getMessage();
            $httpStatus = 503;
        }

        // verifica o redis com um ping
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
