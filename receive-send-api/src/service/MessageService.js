const HttpClient = require("../utils/HttpClient");

class MessageService {
  constructor() {
    this.baseUrl = "http://record-api-py:8001"; // container name e porta do Record-API
  }

  /**
   * Persiste cada mensagem no Record-API => MySQL
   */
  async saveToHistory(userIdSend, userIdReceive, message) {
    const url = `${this.baseUrl}/message`;
    const body = {
      message,
      userIdSend,
      userIdReceive,
    };
    await HttpClient.post(url, body);
  }

  /**
   * Recupera mensagens do canal
   */
  async getMessagesByChannel(channelKey) {
    const url = `${this.baseUrl}/message?channel=${channelKey}`;
    const msgs = await HttpClient.get(url);
    return msgs;
  }
}

module.exports = MessageService;
