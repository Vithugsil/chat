<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Service\AuthService;

class AuthController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /token
     * Body: { "email": "...", "password": "..." }
     * Retorna { token: "JWT" } ou { token: false }
     */
    public function login(Request $request, Response $response, $args)
    {
        $data = json_decode((string) $request->getBody(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $token = $this->authService->login($email, $password);
        if (!$token) {
            $payload = json_encode(['token' => false]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $payload = json_encode(['token' => $token]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * GET /token?user=<userId>
     * Header: { Authorization: "<jwt>" }
     * Retorna { auth: true/false }
     */
    public function validate(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $userId = intval($query['user'] ?? 0);
        $jwt = $request->getHeaderLine('Authorization');

        if (!$userId || !$jwt) {
            $payload = json_encode(['auth' => false]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $isValid = $this->authService->validateToken($userId, $jwt);
        $payload = json_encode(['auth' => $isValid]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
