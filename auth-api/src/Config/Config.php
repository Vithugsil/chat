<?php
namespace App\Config;

class Config
{
    const DB_HOST = 'mysql';
    const DB_PORT = '3306';
    const DB_NAME = 'chatdb';
    const DB_USER = 'chatuser';
    const DB_PASS = 'chatpass';

    const JWT_SECRET = 'SenhaSecretaDoJWT'; 

    const REDIS_HOST = 'redis';
    const REDIS_PORT = 6379;
}
