<?php
namespace App\Model;

use PDO;
use App\Config\Config;

class UserModel
{
    private $pdo;

    public function __construct(int $maxRetries = 10, float $retryInterval = 2.0)
    {
        $attempts = 0;
        while ($attempts < $maxRetries) {
            try {
                $dsn = 'mysql:host=' . Config::DB_HOST
                    . ';port=' . Config::DB_PORT
                    . ';dbname=' . Config::DB_NAME;

                $this->pdo = new PDO(
                    $dsn,
                    Config::DB_USER,
                    Config::DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                return;
            } catch (\PDOException $e) {
                $attempts++;
                if ($attempts >= $maxRetries) {
                    throw new \RuntimeException(
                        "Não foi possível conectar ao MySQL após {$maxRetries} tentativas. Erro: "
                        . $e->getMessage()
                    );
                }
                error_log("[UserModel] MySQL não pronto, tentando novamente em {$retryInterval}s ({$attempts}/{$maxRetries})");
                sleep($retryInterval);
            }
        }
    }

    public function createUser(string $name, string $lastName, string $email, string $password): bool
    {
        if ($this->getUserByEmail($email) !== null) {
            throw new \InvalidArgumentException("Email já cadastrado: {$email}");
        }

        $sql = "INSERT INTO user (name, last_name, email, password) 
            VALUES (:name, :lastName, :email, :password)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':lastName' => $lastName,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT)
        ]);
    }


    public function getUserByEmail(string $email): ?array
    {
        $sql = "SELECT user_id AS id, name, last_name AS lastName, email, password 
                FROM user WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getUserById(int $id): ?array
    {
        $sql = "SELECT user_id AS id, name, last_name AS lastName, email, password 
                FROM user WHERE user_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT user_id AS id, name, last_name AS lastName, email FROM user";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para expor a conexão PDO para o health check
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
