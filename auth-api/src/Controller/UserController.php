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

    public function create(Request $request, Response $response, $args)
    {

        $body = (string) $request->getBody();
        error_log("Request body: $body"); // Log the request body for debugging

        $data2 = json_decode($body, true);
        error_log("Decoded data: " . print_r($data2, true)); // Log the decoded data for debugging

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

        try {
            $ok = $this->userModel->createUser($name, $lastName, $email, $password);
            if (!$ok) {
                $resp = json_encode(['message' => 'erro ao criar usuário']);
                $response->getBody()->write($resp);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (\InvalidArgumentException $e) {
            $resp = json_encode(['message' => $e->getMessage()]);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            $resp = json_encode(['message' => 'erro interno no servidor']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $resp = json_encode([
            'message' => 'ok',
            'user' => [
                'name' => $name,
                'lastName' => $lastName,
                'email' => $email,
                'password' => $password
            ]
        ]);
        $response->getBody()->write($resp);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }


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

        $resp = json_encode([
            'name' => $user['name'],
            'lastName' => $user['lastName'],
            'email' => $user['email'],
            'password' => $user['password']
        ]);
        $response->getBody()->write($resp);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args)
    {
        $userId = intval($args['id'] ?? 0);
        if (!$userId) {
            $resp = json_encode(['message' => 'ID do usuário é obrigatório']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $data = json_decode((string) $request->getBody(), true);
        if (empty($data)) {
            $resp = json_encode(['message' => 'Dados para atualização são obrigatórios']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ok = $this->userModel->updateUser($userId, $data);
            if (!$ok) {
                $resp = json_encode(['message' => 'Erro ao atualizar usuário']);
                $response->getBody()->write($resp);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }

            // Busca o usuário atualizado para retornar
            $user = $this->userModel->getUserById($userId);
            $resp = json_encode([
                'message' => 'Usuário atualizado com sucesso',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'lastName' => $user['lastName'],
                    'email' => $user['email']
                ]
            ]);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\InvalidArgumentException $e) {
            $resp = json_encode(['message' => $e->getMessage()]);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            $resp = json_encode(['message' => 'Erro interno no servidor']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $userId = intval($args['id'] ?? 0);
        if (!$userId) {
            $resp = json_encode(['message' => 'ID do usuário é obrigatório']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ok = $this->userModel->deleteUser($userId);
            if (!$ok) {
                $resp = json_encode(['message' => 'Erro ao deletar usuário']);
                $response->getBody()->write($resp);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }

            $resp = json_encode(['message' => 'Usuário deletado com sucesso']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\InvalidArgumentException $e) {
            $resp = json_encode(['message' => $e->getMessage()]);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            $resp = json_encode(['message' => 'Erro interno no servidor']);
            $response->getBody()->write($resp);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
