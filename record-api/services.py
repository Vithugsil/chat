from models import MessageModel
from utils.RedisCache import RedisCache
import json
import pymysql
from app_config import Config
from redis import Redis

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
    
class ConnectionServiceCheck:
    def __init__(self):
        self.dbConnection = pymysql.connect(
            host=Config.DB_HOST,
            port=Config.DB_PORT,
            user=Config.DB_USER,
            password=Config.DB_PASSWORD,
            database=Config.DB_NAME,
            cursorclass=pymysql.cursors.DictCursor
        )

        self.redisConnection = Redis(
            host=Config.REDIS_HOST,
            port=Config.REDIS_PORT,
        )
    
    def helf_check(self) -> dict:
        try:
            with self.dbConnection.cursor() as cursor:
                cursor.execute("SELECT 1")
                result = cursor.fetchone()
                if result and (list(result.values())[0] == 1 or result.get('1') == 1):
                    print("[ConnectionServiceCheck] MySQL connection is OK", flush=True)
            self.redisConnection.ping()
            print("[ConnectionServiceCheck] Redis connection is OK", flush=True)
            self.dbConnection.close()
            self.redisConnection.close()
            return {"status": "ok"}
        except pymysql.DatabaseError as e:
            print(f"[ConnectionServiceCheck] MySQL connection error: {e}", flush=True)
            return {"status": "error", "message": f"MySQL connection error: {e}"}
        except Exception as e:
            print(f"[ConnectionServiceCheck] Redis connection error: {e}", flush=True)
            return {"status": "error", "message": f"Redis connection error: {e}"}

        

