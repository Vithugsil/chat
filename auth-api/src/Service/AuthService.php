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
        $cacheKey = "jwt_valid:{$userId}:{$jwt}";

        $cached = RedisCache::get($cacheKey);
        if ($cached !== null) {
            error_log("Returning cached result: " . ($cached === "1" ? "true" : "false"));
            return $cached === "1";
        }
        error_log("Cached value: " . var_export($cached, true));

        $decoded = JwtHelper::decodeToken($jwt);
        error_log("Decoded token: " . var_export($decoded, true));
        if (!$decoded || $decoded['userId'] !== $userId) {
            error_log("Token invalid or userId mismatch");
            RedisCache::set($cacheKey, "0", 60);
            return false;
        }

        $user = $this->userModel->getUserById($userId);
        error_log("User from DB: " . var_export($user, true));
        if (!$user || $decoded['password'] !== $user['password']) {
            error_log("User not found or password mismatch");
            RedisCache::set($cacheKey, "0", 60);
            return false;
        }

        error_log("Token validated successfully");
        RedisCache::set($cacheKey, "1", 60);
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
