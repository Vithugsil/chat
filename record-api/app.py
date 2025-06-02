from flask import Flask, request, jsonify
from flask_cors import CORS
from services import MessageService
from services import ConnectionServiceCheck

app = Flask(__name__)
CORS(app)

message_service = MessageService()

@app.route('/health', methods=['GET'])
def health_check():
    try:
        state = ConnectionServiceCheck().helf_check()
        if state["status"] == "ok":
            return jsonify(state), 200
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


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

@app.route('/message', methods=['GET'])
def get_messages():
    user_id_send = request.args.get('user', '')
    if not user_id_send:
        return jsonify([]), 200

    try:
        user_id_send = int(user_id_send)
    except ValueError:
        return jsonify([]), 200

    print(f"[GET /message] filtrando por user_id_send = {user_id_send}")

    msgs = message_service.get_messages_by_user_id_send(user_id_send)
    return jsonify(msgs), 200

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8001)
