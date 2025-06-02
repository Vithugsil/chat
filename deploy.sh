#!/bin/bash

echo "Iniciando deploy em $(date)"

echo "Subindo containers com docker-compose up -d..."
docker-compose up -d

echo "Verificando o estado dos containers..."
docker ps

check_health() {
    local service=$1
    local url=$2
    local max_retries=10
    local retries=0
    echo "Verificando o estado de saúde do serviço: $service..."

    while [ $retries -lt $max_retries ]; do
        response=$(curl --write-out "%{http_code}" --silent --output /dev/null "$url")
        if [ "$response" -eq 200 ]; then
            echo "[$service] Está funcionando corretamente (HTTP 200)"
            return
        fi
        echo "[$service] ERRO ao acessar a rota /health, Código de resposta HTTP: $response"
        retries=$((retries + 1))
        echo "Tentando novamente em 3 segundos..."
        sleep 3
    done

    echo "[$service] Não conseguiu se conectar após $max_retries tentativas"
}


check_health "Node.js API" "http://localhost:8002/health"
check_health "Python API" "http://localhost:8001/health"

echo "Deploy completo em $(date)"
