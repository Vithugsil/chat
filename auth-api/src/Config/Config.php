<?php
namespace App\Config;

class Config
{
    // MySQL
    const DB_HOST = 'mysql';
    const DB_PORT = '3306';
    const DB_NAME = 'chatdb';
    const DB_USER = 'chatuser';
    const DB_PASS = 'chatpass';

    // JWT
    const JWT_SECRET = 'MINHA_CHAVE_SECRETA_JWT'; // troque por algo forte em produção

    // Redis
    const REDIS_HOST = 'redis';
    const REDIS_PORT = 6379;
}
