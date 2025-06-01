const axios = require('axios');

class HttpClient {
  static async get(url, headers = {}) {
    try {
      const resp = await axios.get(url, { headers });
      return resp.data;
    } catch (err) {
      throw err;
    }
  }

  static async post(url, body = {}, headers = {}) {
    try {
      const resp = await axios.post(url, body, { headers });
      return resp.data;
    } catch (err) {
      throw err;
    }
  }
}

module.exports = HttpClient;
