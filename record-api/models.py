# record-api/models.py

import time
import pymysql
from app_config import Config

class MessageModel:
    def __init__(self, max_retries: int = 10, retry_interval: float = 2.0):
        """
        Tenta conectar ao MySQL, aguardando até que o servidor esteja pronto.
        max_retries: máximo de tentativas antes de desistir (padrão: 10).
        retry_interval: segundos entre cada tentativa (padrão: 2s).
        """
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
                # Conectou com sucesso → sai do loop
                break
            except pymysql.err.OperationalError:
                attempts += 1
                print(f"[MessageModel] MySQL não pronto, tentando em {retry_interval}s ({attempts}/{max_retries})")
                time.sleep(retry_interval)
        else:
            # Se esgotou as tentativas
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

    def get_messages_by_channel(self, channel: str) -> list:
        with self.conn.cursor() as cursor:
            sql = """
                SELECT user_id_send, user_id_receive, message
                FROM message
                WHERE CONCAT(user_id_send, user_id_receive) = %s
                ORDER BY created_at ASC
            """
            cursor.execute(sql, (channel))
            result = cursor.fetchall()
        return result
