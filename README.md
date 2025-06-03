_Comandos de build:_ \
docker-compose -d -> caso queira rodar manualmente. \
ou no bash, na pasta raiz execute: ./deploy.sh

diagrama dos containers: \
![Diagrama sem nome drawio (3)](https://github.com/user-attachments/assets/6508e624-30a4-4695-a32f-5b940af5d6a0) \

**Fluxo dos Testes executados pelo deploy.sh**  
**Criação de usuários na API PHP:**

**Envia POST para /user para criar o usuário 1:**
- Nome: Usuario 1
- Email: usuario1@email.com
- Senha: senha123

**Envia POST para /user para criar o usuário 2:**
- Nome: Usuario 2
- Email: usuario2@email.com
- Senha: senha456

**Autenticação e obtenção do token JWT:**
- Envia POST para /token com email e senha do usuário 1 para obter token de autenticação.
- O token JWT recebido é extraído da resposta para uso nas chamadas subsequentes.

**Validação do token JWT:**
- Envia GET para /token?user=1 com o header Authorization contendo o token para validar se o token é válido e pertence ao usuário 1.

**Envio de mensagens para a fila via Receive-Send API:**
Envia 3 requisições POST para /message na porta 8002, com o token de autenticação no header:
- Mensagem 1: "Eai, tudo bem?" (de usuário 1 para usuário 2)
- Mensagem 2: "Bora tomar uma?" (de usuário 1 para usuário 2)
- Mensagem 3: "Vamo la no bar" (de usuário 1 para usuário 2)

Internamente, o /message chama o endpoint /message/worker para processar a fila de mensagens.

**Processamento e armazenamento das mensagens da fila no banco (via /message/worker):**
- O /message/worker verifica a autenticação e, após autenticar o usuário, drena a fila de mensagens para o canal correspondente (userIdSend:userIdReceive).
- As mensagens drenadas são salvas no histórico de mensagens (banco de dados) através de messageService.saveToHistory.

**Verificação das mensagens armazenadas no banco de dados:**
- Envia GET para /message?user=1 para listar as mensagens enviadas pelo usuário 1 que foram processadas e armazenadas no banco.

**Finalização do teste:**
Exibe data e hora da conclusão do deploy/testes.
