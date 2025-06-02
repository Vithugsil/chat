const HttpClient = require("../utils/HttpClient");
const RedisCache = require("../utils/RedisCache");

class AuthService {
  constructor() {
    this.baseUrl = "http://auth-api-php:8000"; // container name e porta
    this.cache = new RedisCache();
  }

  async isUserAuthenticated(userId, token) {
    const cacheKey = `authok:${userId}:${token}`;
    const cached = await this.cache.get(cacheKey);
    if (cached !== null) {
      return cached === "1";
    }
    try {
      const url = `${this.baseUrl}/token?user=${userId}`;
      const headers = { Authorization: token };
      const resp = await HttpClient.get(url, headers);
      console.log(`Auth response for user ${userId}:`, resp);
      const auth = resp.auth === true;
      await this.cache.set(cacheKey, auth ? "1" : "0", 90);
      return auth;
    } catch (err) {
      return false;
    }
  }

  async getAllUsers() {
    const cacheKey = "all_users";
    const cached = await this.cache.get(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    try {
      const url = `${this.baseUrl}/allUsers`;
      const users = await HttpClient.get(url);
      await this.cache.set(cacheKey, JSON.stringify(users), 60);
      return users;
    } catch (err) {
      return [];
    }
  }
}

module.exports = AuthService;
