<?php
namespace App\Service;

use App\Model\UserModel;
use App\Utils\JwtHelper;
use App\Utils\RedisCache;

class AuthService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login(string $email, string $password): ?string
    {
        $cacheKey = "user_by_email:{$email}";
        $cached = RedisCache::get($cacheKey);
        if ($cached) {
            $user = json_decode($cached, true);
        } else {
            $user = $this->userModel->getUserByEmail($email);
            if ($user) {
                RedisCache::set($cacheKey, json_encode($user), 300);
            }
        }

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        return JwtHelper::generateToken($user['id'], $user['password']);
    }

    public function validateToken(int $userId, string $jwt): bool
    {

        $decoded = JwtHelper::decodeToken($jwt);
        error_log("Decoded token: " . var_export($decoded, true));
        if (!$decoded || $decoded['userId'] !== $userId) {
            error_log("Token invalid or userId mismatch");
            return false;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user || $decoded['password'] !== $user['password']) {
            error_log("User not found or password mismatch");
            return false;
        }

        error_log("Token validated successfully");
        return true;
    }

    public function getAllUsers(): array
    {
        $cacheKey = "all_users";
        $cached = RedisCache::get($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }

        // Usa diretamente o método do model
        $users = $this->userModel->getAllUsers();
        RedisCache::set($cacheKey, json_encode($users), 300);
        return $users;
    }
}
