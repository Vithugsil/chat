from models import MessageModel
from utils.RedisCache import RedisCache
import json

class MessageService:
    def __init__(self):
        self.model = MessageModel()

    def create_message(self, user_id_send: int, user_id_receive: int, message: str) -> bool:
        # Insere no banco
        return self.model.insert_message(user_id_send, user_id_receive, message)

    def get_messages(self, user_id_send: int, user_id_receive: int) -> list:
        # Monta canal
        channel = f"{user_id_send}{user_id_receive}"
        cache_key = f"msg_channel:{channel}"
        cached = RedisCache.get(cache_key)
        if cached:
            return json.loads(cached)

        print(channel)
        # Não em cache → busca no DB
        msgs = self.model.get_messages_by_channel(channel)
        # Cache por 30s
        RedisCache.set(cache_key, json.dumps(msgs), 30)
        return msgs
