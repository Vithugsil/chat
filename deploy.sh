#!/bin/bash

echo "Iniciando deploy em $(date)"

echo "Subindo containers com docker-compose up -d..."
docker-compose up -d --build

echo
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
        echo "Tentando novamente em 5 segundos..."
        sleep 5
    done

    echo "[$service] Não conseguiu se conectar após $max_retries tentativas"
}

echo ""
check_health "Node.js API" "http://localhost:8002/health"
echo ""
check_health "Python API" "http://localhost:8001/health"
echo ""
check_health "PHP Auth API" "http://localhost:8000/health"

echo ""
echo "Criando usuário de teste na API PHP..."

curl --request POST \
  --url http://localhost:8000/user \
  --header "Content-Type: application/json" \
  --data '{"name": "Usuario 1","lastName":"um","email":"usuario1@email.com","password":"senha123"}'

echo ""
curl --request POST \
  --url http://localhost:8000/user \
  --header "Content-Type: application/json" \
  --data '{"name": "Usuario 2","lastName":"dois","email":"usuario2@email.com","password":"senha456"}'

echo ""

echo "Criando token de autenticação..."

response=$(curl --silent --request POST \
  --url http://localhost:8000/token \
  --header 'Content-Type: application/json' \
  --data '{
    "email": "usuario1@email.com",
    "password": "senha123"
}')

token=$(echo "$response" | grep -oP '(?<="token":")[^"]+')


echo "Token recebido: $token"
echo

echo "validando o token de autenticação..."
curl --request GET \
  --url 'http://localhost:8000/token?user=1' \
  --header "Authorization: $token" \

echo
echo "enviando mensagem para a fila..."
curl --request POST \
  --url http://localhost:8002/message \
  --header "Authorization: $token" \
  --header 'Content-Type: application/json' \
  --data '{  
	"userIdSend": 1,
 	"userIdReceive": 2,         
 	"message": "Eai, tudo bem?"       
}'
echo
echo "Enviando mais mensagens para a fila..."
curl --request POST \
  --url http://localhost:8002/message \
  --header "Authorization: $token" \
  --header 'Content-Type: application/json' \
  --data '{  
	"userIdSend": 1,
 	"userIdReceive": 2,         
 	"message": "Bora tomar uma?"       
}'
echo
echo "Enviando mais mensagens para a fila..."
curl --request POST \
  --url http://localhost:8002/message \
  --header "Authorization: $token" \
  --header 'Content-Type: application/json' \
  --data '{  
	"userIdSend": 1,
 	"userIdReceive": 2,         
 	"message": "Vamo la no bar"       
}'

echo 
curl --request POST \
  --url http://localhost:8002/message/worker \
  --header "Authorization: $token" \
  --header 'Content-Type: application/json' \
  --data '{
	"userIdSend": 1,         
 "userIdReceive": 2       
}'

echo ""
echo "Eviando mensagens da fila para o banco de dados..."
echo 
echo "Verificando mensagens no banco de dados..."
curl --request GET \
  --url 'http://localhost:8002/message?user=1' \
  --header "Authorization: $token" \

echo
echo "Deploy completo em $(date)"
