<?php
namespace App\Utils;

use PDO;

class DatabaseUtils
{
    // verifica se a conexão com o banco de dados está ativa pelo PDO (php data objects) que retorna o status da conexão
    public static function checkConnection(PDO $pdo): bool
    {
        try {
            return $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== null;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Erro na conexão com MySQL: " . $e->getMessage());
        }
    }
} 