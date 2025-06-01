from flask import Flask, request, jsonify
from flask_cors import CORS
from services import MessageService

app = Flask(__name__)
CORS(app)

message_service = MessageService()

# POST /message
# Body: { message, userIdSend, userIdReceive }
@app.route('/message', methods=['POST'])
def create_message():
    data = request.json or {}
    message       = data.get('message')
    user_id_send  = data.get('userIdSend')
    user_id_rec   = data.get('userIdReceive')

    if not message or not user_id_send or not user_id_rec:
        return jsonify({'error': 'dados insuficientes'}), 400

    ok = message_service.create_message(user_id_send, user_id_rec, message)
    if not ok:
        return jsonify({'error': 'erro ao inserir mensagem'}), 500

    return jsonify({'ok': True}), 201

# GET /message?channel=<channel>
@app.route('/message', methods=['GET'])
def get_messages():
    channel = request.args.get('channel', '')
    if not channel:
        return jsonify([]), 400

    # Extrai userIdSend e userIdReceive de channel
    # Vamos supor que ambos sejam dígitos sem ambiguidade, pois concatenamos como string
    # Ex: channel="14" → user_send=1, user_rec=4
    # Em casos reais, usar separador (ex: "1-4"), mas aqui seguimos enunciado.
    # Para simplicidade, se o canal tiver dois ou mais dígitos, precisamos sabê-lo de antemão.
    # Por isso, no Receive-Send-API, vamos concatenar sem ambiguidade (ex: `${u1}-${u2}`).
    print(f"[GET /message] channel: {channel}")
    msgs = message_service.get_messages(channel[0], channel[1])
    return jsonify(msgs)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8001)
