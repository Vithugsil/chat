const HttpClient = require("../utils/HttpClient");

class MessageService {
  constructor() {
    this.baseUrl = "http://record-api-py:8001";
  }

  async saveToHistory(userIdSend, userIdReceive, message) {
    const url = `${this.baseUrl}/message`;
    const body = {
      message,
      userIdSend,
      userIdReceive,
    };
    await HttpClient.post(url, body);
  }

  async getMessagesById(userId) {
    const url = `${this.baseUrl}/message?user=${userId}`;
    const msgs = await HttpClient.get(url);
    return msgs;
  }
}

module.exports = MessageService;
