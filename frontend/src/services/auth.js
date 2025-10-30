import api from './api';

const TOKEN_KEY = 'jwt_token';

const authService = {
  /**
   * Login user with username and password
   */
  async login(username, password) {
    try {
      const response = await api.post('/login', {
        username,
        password,
      });
      
      // Store the JWT token
      if (response.data.token) {
        localStorage.setItem(TOKEN_KEY, response.data.token);
      }
      
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Login failed. Please check your credentials.',
      };
    }
  },

  /**
   * Get current authenticated user
   */
  async getCurrentUser() {
    try {
      const response = await api.get('/me');
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, error: error.response?.data?.message };
    }
  },

  /**
   * Logout current user
   */
  async logout() {
    // Remove token
    localStorage.removeItem(TOKEN_KEY);
    return { success: true };
  },

  /**
   * Get stored token
   */
  getToken() {
    return localStorage.getItem(TOKEN_KEY);
  },

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    // Simply check if token exists (fast, no API call)
    return !!this.getToken();
  },
};

export default authService;
