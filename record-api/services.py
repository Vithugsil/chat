from models import MessageModel
from utils.RedisCache import RedisCache
import json

class MessageService:
    def __init__(self):
        self.model = MessageModel()

    def create_message(self, user_id_send: int, user_id_receive: int, message: str) -> bool:
        return self.model.insert_message(user_id_send, user_id_receive, message)

    def get_messages_by_user_id_send(self, user_id_send: int) -> list:
        cache_key = f"msg_sender:{user_id_send}"
        cached = RedisCache.get(cache_key)
        if cached:
            return json.loads(cached)

        msgs = self.model.get_messages_by_user_id_send(user_id_send)

        RedisCache.set(cache_key, json.dumps(msgs), 30)
        return msgs
    
    def helf_check(self) -> bool:
        try:
            # Check SQL connection
            self.model.conn.ping(reconnect=True)

            # Check Redis connection
            RedisCache._redis.ping()

            print("[MessageService] Health check passed")
            return True
        except Exception as e:
            print(f"[MessageService] Health check failed: {e}")
            return False
