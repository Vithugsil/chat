<?php
namespace App\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\Config;

class JwtHelper
{
    public static function generateToken(int $userId, string $password, string $email, string $name, string $lastName): string
    {
        $payload = [
            'userId' => $userId,
            'name' => $name,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
            'iat' => time(),
            'exp' => time() + 3600
        ];
        return JWT::encode($payload, Config::JWT_SECRET, 'HS256');
    }

    public static function decodeToken(string $token): ?array
    {
        try {
            $decoded = (array) JWT::decode($token, new Key(Config::JWT_SECRET, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}
