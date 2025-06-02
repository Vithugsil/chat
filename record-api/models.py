import time
import pymysql
from app_config import Config

class MessageModel:
    def __init__(self, max_retries: int = 10, retry_interval: float = 2.0):
        self.conn = None
        attempts = 0
        while attempts < max_retries:
            try:
                self.conn = pymysql.connect(
                    host=Config.DB_HOST,
                    port=Config.DB_PORT,
                    user=Config.DB_USER,
                    password=Config.DB_PASSWORD,
                    database=Config.DB_NAME,
                    cursorclass=pymysql.cursors.DictCursor
                )
                break
            except pymysql.err.OperationalError:
                attempts += 1
                print(f"[MessageModel] MySQL não pronto, tentando em {retry_interval}s ({attempts}/{max_retries})")
                time.sleep(retry_interval)
        else:
            raise RuntimeError(f"Não foi possível conectar ao MySQL após {max_retries} tentativas.")

    def insert_message(self, user_id_send: int, user_id_receive: int, message: str) -> bool:
        with self.conn.cursor() as cursor:
            sql = """
                INSERT INTO message (message, user_id_send, user_id_receive)
                VALUES (%s, %s, %s)
            """
            cursor.execute(sql, (message, user_id_send, user_id_receive))
            self.conn.commit()
        return True

    def get_messages_by_user_id_send(self, user_id_send: int) -> list:
        with self.conn.cursor() as cursor:
            sql = """
                SELECT user_id_send, user_id_receive, message
                FROM message
                WHERE user_id_send = %s OR user_id_receive = %s
                ORDER BY created_at ASC
            """
            cursor.execute(sql, (user_id_send, user_id_send))
            result = cursor.fetchall()
        return result
