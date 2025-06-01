<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\UserModel;

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * POST /user
     * Body: { name, lastName, email, password }
     * Retorna { message: 'ok', user: {...} } ou erro
     */
    public function create(Request $request, Response $response, $args)
    {
        $data = json_decode((string) $request->getBody(), true);
        $name = $data['name'] ?? '';
        $lastName = $data['lastName'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$name || !$lastName || !$email || !$password) {
            $resp = json_encode(['message' => 'dados insuficientes']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $ok = $this->userModel->createUser($name, $lastName, $email, $password);
        if (!$ok) {
            $resp = json_encode(['message' => 'erro ao criar usuário']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $resp = json_encode([
            'message' => 'ok',
            'user' => [
                'name' => $name,
                'lastName' => $lastName,
                'email' => $email,
                'password' => $password // Em geral não retorna a senha, mas respeitando o enunciado
            ]
        ]);
        $response->getBody()->write($resp);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    /**
     * GET /user?email=<email>
     * Retorna { name, lastName, email, password } ou {}
     */
    public function getByEmail(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $email = $query['email'] ?? '';
        if (!$email) {
            $resp = json_encode([]);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            $resp = json_encode([]);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Retorna os campos conforme enunciado
        $resp = json_encode([
            'name' => $user['name'],
            'lastName' => $user['lastName'],
            'email' => $user['email'],
            'password' => $user['password']
        ]);
        $response->getBody()->write($resp);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
